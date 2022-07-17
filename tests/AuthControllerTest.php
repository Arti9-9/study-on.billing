<?php

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;

class AuthControllerTest extends AbstractTest
{
    private string $startingPath = '/api/v1';
    private SerializerInterface $serializer;

    public function getFixtures(): array
    {
        return [UserFixtures::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    public function testAuthSuccessfully(): void
    {
        $user = [
            'username' => 'user@mail.ru',
            'password' => 'user123',
        ];

        $client = self::getClient();
        $client->jsonRequest(
            'POST',
            $this->startingPath . '/auth',
            $user
        );

        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        self::assertNotEmpty($json['token']);
    }

    public function testAuthNonExistingUser(): void
    {
        $user = [
            'username' => 'test@mail.ru',
            'password' => 'Qwerty123'
        ];

        $client = self::getClient();
        $client->jsonRequest(
            'POST',
            $this->startingPath . '/auth',
            $user
        );

        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED, $client->getResponse());

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        self::assertNotEmpty($json['code']);
        self::assertNotEmpty($json['message']);

        self::assertEquals('401', $json['code']);
        self::assertEquals('Inv als.', $json['message']);
    }

    public function testAuthWithWrongPassword(): void
    {
        $user = [
            'username' => 'user@mail.ru',
            'password' => 'user1234',
        ];

        $client = self::getClient();
        $client->jsonRequest(
            'POST',
            $this->startingPath . '/auth',
            $user
        );

        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED, $client->getResponse());

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertNotEmpty($json['message']);
    }

    public function testRegisterSuccessfully(): void
    {
        $user = [
            'username' => 'newuser@mail.ru',
            'password' => 'newuser123',
        ];

        $client = self::getClient();
        $client->jsonRequest(
            'POST',
            $this->startingPath . '/register',
            $user
        );

        $this->assertResponseCode(Response::HTTP_CREATED, $client->getResponse());

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertNotEmpty($json['token']);

        $entityManager = self::getEntityManager();
        $newUser = $entityManager->getRepository(User::class)->findOneBy([
            'email' => 'newuser@mail.ru',
        ]);

        self::assertNotNull($newUser);
        self::assertEquals(0, $newUser->getBalance());
        self::assertEquals(["ROLE_USER"], $newUser->getRoles());
    }

    public function testRegisterExistingUser(): void
    {
        $user = [
            'username' => 'user@mail.ru',
            'password' => 'user123',
        ];

        $client = self::getClient();
        $client->jsonRequest(
            'POST',
            $this->startingPath . '/register',
            $user
        );

        $this->assertResponseCode(Response::HTTP_FORBIDDEN, $client->getResponse());

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertEquals('Пользователь с данным email уже существует', $json['message']);
    }
}