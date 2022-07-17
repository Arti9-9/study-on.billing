<?php


namespace App\Tests;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Model\userDTO;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends AbstractTest
{
    /**
     * @var string
     */
    private $startingPath = '/api/v1';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function getFixtures(): array
    {

        return [UserFixtures::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    public function testCurrent(): void
    {

        // Авторизируемся существующим пользователем
        $user = [
            'username' => 'user@mail.ru',
            'password' => 'user123',
        ];

        // Формируем запрос
        $client = self::getClient();
        $client->request(
            'POST',
            $this->startingPath . '/auth',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );
        $json = json_decode($client->getResponse()->getContent(), true);
        // Получаем токен клиента
        $token = $json['token'];

        //_____________Проверка успешной операции получения данных_____________
        // Формирование верного запроса
        $contentHeaders = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ];

        $client->request(
            'GET',
            $this->startingPath . '/users/current',
            [],
            [],
            $contentHeaders
        );
        // Проверка статуса ответа, 200
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа, что он действительно в формате json
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        /** @var UserDTO $responseUserDTO */
        $responseUserDTO = $this->serializer->deserialize($client->getResponse()->getContent(), userDTO::class, 'json');

        // Получим данные о пользователе из бд и сравним
        $em = self::getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $responseUserDTO->username]);
        // Сравнение данных
        self::assertEquals($responseUserDTO->username, $user->getEmail());
        self::assertEquals($responseUserDTO->roles[0], $user->getRoles()[0]);
        self::assertEquals($responseUserDTO->balance, $user->getBalance());

        //_____________Проверка неуспешной операции (jwt токен неверный)_____________
        $token = 'шишль мышль';
        // Передаем неверный токен
        $contentHeaders = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ];

        $client->request(
            'GET',
            $this->startingPath . '/users/current',
            [],
            [],
            $contentHeaders
        );
        // Проверка статуса ответа, 401
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED, $client->getResponse());
    }
}