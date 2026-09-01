<?php

declare(strict_types=1);

namespace XBoard\Tests;

use PHPUnit\Framework\TestCase;
use XBoard\BoardType;
use XBoard\Customers\Composer;
use XBoard\Customers\Post;
use XBoard\Errors\AuthenticationError;
use XBoard\FileUpload;
use XBoard\XBoard;

final class XBoardTest extends TestCase
{
    public function testExchangesThenCreatesACustomerPostWithoutEnsureBoard(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::exchangeOk(),
            MockClient::json(200, [
                'message' => 'Customer shared post created.',
                'status' => 1,
                'data' => [
                    'boardType' => 'shared',
                    'boardID' => 'shared-1',
                    'boardCreated' => true,
                    'postID' => 'post-1',
                ],
            ]),
        ], $history);

        $post = $client->customers->posts()->create(
            externalCustomerId: 'CRM-1001',
            boardType: BoardType::Shared,
            title: 'Kickoff',
        );
        $this->assertSame('post-1', $post->id);
        $this->assertCount(2, $history);

        $exchange = MockClient::requestAt($history, 0);
        $this->assertStringEndsWith('/auth/api-keys/exchange', (string) $exchange->getUri());

        $create = MockClient::requestAt($history, 1);
        $this->assertSame('POST', $create->getMethod());
        $this->assertStringEndsWith('/people/account/customer/post', (string) $create->getUri());
        $this->assertSame([
            'boardType' => 'shared',
            'externalID' => 'CRM-1001',
            'title' => 'Kickoff',
        ], json_decode((string) $create->getBody(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testComposeCreateAutoEnsuresThenAppendsPartsInOrder(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::exchangeOk(),
            MockClient::json(200, [
                'message' => 'ok',
                'status' => 1,
                'data' => ['postID' => 'post-1', 'boardID' => 'shared-1', 'boardType' => 'shared'],
            ]),
            MockClient::json(200, ['message' => 'ok', 'status' => 1, 'data' => ['_id' => 'n1']]),
            MockClient::json(200, [
                'message' => 'queued',
                'status' => 1,
                'data' => ['_id' => 'f1', 'meta' => ['fileProcStatus' => 'PROCESSING']],
            ]),
            MockClient::json(200, ['message' => 'ok', 'status' => 1, 'data' => ['_id' => 'n2']]),
        ], $history);

        $post = $client->customers->posts()
            ->compose(externalCustomerId: 'CRM-1001', boardType: BoardType::Shared)
            ->setTitle('Kickoff')
            ->addNote('content 1')
            ->addFile(new FileUpload('hi', 'hello.txt', 'text/plain'))
            ->addNote('content 2')
            ->create();

        $this->assertSame('post-1', $post->id);
        $this->assertStringEndsWith('/people/account/customer/post', (string) MockClient::requestAt($history, 1)->getUri());
        $this->assertStringEndsWith('/people/account/customer/post/note', (string) MockClient::requestAt($history, 2)->getUri());
        $fileReq = MockClient::requestAt($history, 3);
        $this->assertSame('POST', $fileReq->getMethod());
        $this->assertStringContainsString('/people/account/customer/post/file', (string) $fileReq->getUri());
        $this->assertStringContainsString('postId=post-1', (string) $fileReq->getUri());
        $this->assertStringContainsString('multipart/form-data', $fileReq->getHeaderLine('Content-Type'));
        $this->assertStringNotContainsString('application/json', $fileReq->getHeaderLine('Content-Type'));
        $this->assertStringEndsWith('/people/account/customer/post/note', (string) MockClient::requestAt($history, 4)->getUri());
    }

    public function testComposeUpdateAppendsOnExistingPostAndReplacesTitle(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::exchangeOk(),
            MockClient::json(200, [
                'message' => 'ok',
                'status' => 1,
                'data' => ['postID' => 'post-1'],
            ]),
            MockClient::json(200, ['message' => 'ok', 'status' => 1]),
            MockClient::json(200, ['message' => 'ok', 'status' => 1, 'data' => ['_id' => 'n1']]),
        ], $history);

        $post = $client->customers->posts()->get('post-1');
        $post->compose()
            ->setTitle('Renamed')
            ->addNote('appended note')
            ->update();

        $title = MockClient::requestAt($history, 2);
        $this->assertSame('PUT', $title->getMethod());
        $this->assertStringEndsWith('/people/account/customer/post/title', (string) $title->getUri());
        $this->assertSame(
            ['postId' => 'post-1', 'title' => 'Renamed'],
            json_decode((string) $title->getBody(), true, 512, JSON_THROW_ON_ERROR),
        );
        $note = MockClient::requestAt($history, 3);
        $this->assertStringEndsWith('/people/account/customer/post/note', (string) $note->getUri());
    }

    public function testUpdateWithoutAPostThrows(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([MockClient::exchangeOk()], $history);
        $this->expectException(\LogicException::class);
        $client->customers->posts()
            ->compose(externalCustomerId: 'CRM-1001', boardType: BoardType::Shared)
            ->addNote('hello')
            ->update();
    }

    public function testCreateOnExistingPostComposerThrows(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::json(200, [
                'access_token' => 'jwt',
                'token_type' => 'Bearer',
                'expires_in' => 900,
            ]),
            MockClient::json(200, ['message' => 'ok', 'status' => 1, 'data' => ['postID' => 'post-1']]),
        ], $history);

        $post = $client->customers->posts()->get('post-1');
        $this->expectException(\LogicException::class);
        $post->compose()->addNote('nope')->create();
    }

    public function testSetTitleTwiceThrows(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([MockClient::exchangeOk()], $history);
        $this->expectException(\LogicException::class);
        $client->customers->posts()
            ->compose(externalCustomerId: 'CRM-1001', boardType: BoardType::Shared)
            ->setTitle('One')
            ->setTitle('Two');
    }

    public function testHasNoSaveOrDeleteMethods(): void
    {
        $this->assertFalse(method_exists(Composer::class, 'save'));
        $this->assertFalse(method_exists(Composer::class, 'title'));
        $this->assertFalse(method_exists(Composer::class, 'note'));
        $this->assertFalse(method_exists(Composer::class, 'file'));
        $this->assertFalse(method_exists(Post::class, 'save'));
        $this->assertFalse(method_exists(Post::class, 'delete'));
        $this->assertFalse(method_exists(XBoard::class, 'boards'));
        $this->assertFalse(property_exists(XBoard::class, 'boards'));
        $this->assertFalse(property_exists(XBoard::class, 'posts'));
        $this->assertFalse(property_exists(XBoard::class, 'notes'));
        $this->assertFalse(property_exists(XBoard::class, 'files'));
    }

    public function testOptionalBoardEnsureThenListsPosts(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::exchangeOk(),
            MockClient::json(200, [
                'message' => 'Customer shared board ready.',
                'status' => 1,
                'data' => ['boardType' => 'shared', 'boardID' => 'shared-1', 'created' => true],
            ]),
            MockClient::json(200, [
                'message' => 'ok',
                'status' => 1,
                'data' => ['info' => [], 'total' => 0],
            ]),
        ], $history);

        $board = $client->customers->board('CRM-1001', BoardType::Shared);
        $listed = $board->posts()->list();
        $this->assertSame([], $listed);
        $this->assertStringEndsWith('/people/account/customer/board', (string) MockClient::requestAt($history, 1)->getUri());
        $this->assertStringEndsWith('/people/account/customer/posts', (string) MockClient::requestAt($history, 2)->getUri());
    }

    public function testReusesAFreshAccessTokenAcrossCalls(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::exchangeOk(),
            MockClient::json(200, [
                'message' => 'ok',
                'status' => 1,
                'data' => ['info' => [], 'total' => 0],
            ]),
            MockClient::json(200, [
                'message' => 'ok',
                'status' => 1,
                'data' => ['postID' => 'p1'],
            ]),
        ], $history);

        $client->customers->posts()->list('CRM-1001', BoardType::Shared);
        $client->customers->posts()->get('p1');

        $exchangeCalls = array_values(array_filter(
            $history->getArrayCopy(),
            static function (mixed $entry): bool {
                if (!is_array($entry) || !isset($entry['request'])) {
                    return false;
                }
                $request = $entry['request'];
                if (!$request instanceof \Psr\Http\Message\RequestInterface) {
                    return false;
                }

                return str_contains((string) $request->getUri(), '/auth/api-keys/exchange');
            },
        ));
        $this->assertCount(1, $exchangeCalls);
    }

    public function testListReturnsPostObjectsIndexableByOffset(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::exchangeOk(),
            MockClient::json(200, [
                'message' => 'ok',
                'status' => 1,
                'data' => [
                    'info' => [
                        ['_id' => 'post-1'],
                        ['postID' => 'post-2'],
                    ],
                    'total' => 2,
                ],
            ]),
            MockClient::json(200, [
                'message' => 'ok',
                'status' => 1,
                'data' => ['info' => []],
            ]),
        ], $history);

        $listed = $client->customers->posts()->list('CRM-1001', BoardType::Shared);
        $this->assertCount(2, $listed);
        $this->assertInstanceOf(Post::class, $listed[0]);
        $this->assertSame('post-1', $listed[0]->id);
        $this->assertSame('post-2', $listed[1]->id);

        $post = $listed[0];
        $notes = $post->notes()->list();
        $this->assertSame(0, $notes['data']['total']);
    }

    public function testMaps401ToAuthenticationError(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::json(200, [
                'access_token' => 'jwt',
                'token_type' => 'Bearer',
                'expires_in' => 900,
            ]),
            MockClient::json(401, ['message' => 'Unauthorized', 'status' => 0]),
        ], $history);

        $this->expectException(AuthenticationError::class);
        $client->customers->posts()->list('CRM-1001', BoardType::Shared);
    }

    public function testListsNotesFromPartnerNotesEndpoint(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::exchangeOk(),
            MockClient::json(200, ['message' => 'ok', 'status' => 1, 'data' => ['postID' => 'post-1']]),
            MockClient::json(200, [
                'message' => 'ok',
                'status' => 1,
                'data' => [
                    'info' => [
                        ['_id' => 'n1', 'type' => 'NOTE', 'meta' => ['body' => '<p>hello</p>', 'bodyRaw' => 'hello']],
                        ['_id' => 'i1', 'type' => 'FILE', 'meta' => ['fileName' => 'a.pdf']],
                    ],
                ],
            ]),
        ], $history);

        $post = $client->customers->posts()->get('post-1');
        $notes = $post->notes()->list();
        $this->assertCount(2, $notes['data']['info']);
        $this->assertSame('n1', $notes['data']['info'][0]['_id']);
        $this->assertSame('hello', $notes['data']['info'][0]['bodyRaw']);
        $this->assertSame('i1', $notes['data']['info'][1]['_id']);
        $this->assertSame('FILE', $notes['data']['info'][1]['type']);
        $this->assertStringEndsWith('/people/account/customer/post/notes', (string) MockClient::requestAt($history, 2)->getUri());
    }

    public function testSetTitleWithoutExchangingWhenSeededWithAccessToken(): void
    {
        $history = new \ArrayObject();
        $client = MockClient::xboard([
            MockClient::json(200, ['message' => 'ok', 'status' => 1, 'data' => ['postID' => 'post-1']]),
            MockClient::json(200, ['message' => 'ok', 'status' => 1]),
        ], $history, [
            'apiKey' => '',
            'accessToken' => 'seeded-jwt',
        ]);

        $post = $client->customers->posts()->get('post-1');
        $post->setTitle('Kickoff');
        $request = MockClient::requestAt($history, 1);
        $this->assertCount(2, $history);
        $this->assertStringNotContainsString('/exchange', (string) MockClient::requestAt($history, 0)->getUri());
        $this->assertStringEndsWith('/people/account/customer/post/title', (string) $request->getUri());
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('Bearer seeded-jwt', $request->getHeaderLine('Authorization'));
    }
}
