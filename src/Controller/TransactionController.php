<?php


namespace App\Controller;

use App\Entity\User;
use App\Model\TransactionDTO;
use App\Repository\CourseRepository;
use App\Repository\TransactionRepository;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/transactions")
 */
class TransactionController extends AbstractController
{
    /**
     * @OA\Get(
     *     tags={"Transactions"},
     *     path="/api/v1/transactions/",
     *     description="История начислений и списаний текущего пользователя",
     *     summary="История начислений и списаний текущего пользователя",
     *     security={
     *         { "Bearer":{} },
     *     },
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Тип транзакции [payment | deposit]",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="course_code",
     *         in="query",
     *         description="Символьный код курса",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="skip_expired",
     *         in="query",
     *         description="Отбросить записи с датой expires_at оплаты аренд, которые уже истекли",
     *         @OA\Schema(type="bool")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список транзакций",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="id",
     *                         type="int"
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string"
     *                     ),
     *                     @OA\Property(
     *                         property="type",
     *                         type="string"
     *                     ),
     *                     @OA\Property(
     *                         property="course_code",
     *                         type="string"
     *                     ),
     *                     @OA\Property(
     *                         property="amount",
     *                         type="number"
     *                     ),
     *                      @OA\Property(
     *                         property="expires_at",
     *                         type="string"
     *                     ),
     *                 )
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неверный JWT Token",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="401",
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Неверный JWT Token",
     *                 ),
     *             ),
     *        )
     *     )
     * )
     * @Route("/", name="transactions_history", methods={"GET"})
     */
    public function transactions(
        TransactionRepository $transactionRepository,
        CourseRepository $courseRepository,
        Request $request,
        SerializerInterface $serializer
    ): Response {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $transactions = $transactionRepository->findTransactionsByFilter(
                $user,
                $request,
                $courseRepository
            );
            $transactionsDTO = [];
            foreach ($transactions as $transaction) {
                $course = $transaction->getCourse();
                $expiresAt = null;
                if (isset($course) && $course->getTypeFormatString() ==='rent') {
                    $expiresAt = $transaction->getExpiresAt()->format('Y-m-d T H:i:s');
                }
                $transactionsDTO[] = new TransactionDTO(
                    $transaction->getId(),
                    $transaction->getCreatedAt()->format('Y-m-d T H:i:s'),
                    $transaction->getTypeOperationFormatString(),
                    $transaction->getAmount(),
                    $course ? $course->getCode() : null,
                    $expiresAt
                );
            }

            $response = new Response();
// Статус ответа
            $response->setStatusCode(Response::HTTP_OK);
// Передаем данные
            $response->setContent($serializer->serialize($transactionsDTO, 'json'));
// Устанавливаем заголовок ( формат json )
            $response->headers->add(['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $response = new JsonResponse();
            $response->setContent($serializer->serialize($e->getMessage(), 'json'));
        }

        return $response;
    }
}