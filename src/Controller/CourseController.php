<?php


namespace App\Controller;

use App\Entity\Course;
use App\Entity\User;
use App\Model\CourseDTO;
use App\Model\PayDTO;
use App\Repository\CourseRepository;
use App\Service\PaymentService;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/courses")
 */
class CourseController extends AbstractController
{
    /**
     * @Route("", name="courses_index", methods={"GET"})
     */
    public function index(SerializerInterface $serializer, CourseRepository $courseRepository): Response
    {

        // Получаем все курсы
        $courses = $courseRepository->findAll();
        $coursesDto = array();
        foreach ($courses as $course) {
            $coursesDTO[] = new CourseDTO(
                $course->getCode(),
                $course->getTypeFormatString(),
                $course->getPrice(),
            );
        }
        $response = new JsonResponse();
        // Передаем наши курсы
        $response->setContent($serializer->serialize($coursesDTO, 'json'));
        return $response;
    }

    /**
     * @Route("/{code}", name="course_show", methods={"GET"})
     */
    public function show(string $code, SerializerInterface $serializer, CourseRepository $courseRepository): Response
    {

        $course = $courseRepository->findOneBy(['code' => $code]);
        $statusCode = '';
        $dataResponse = [];
        if (!isset($course)) {
            $dataResponse = [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Данный курс не найден',
            ];
            $statusCode = Response::HTTP_NOT_FOUND;
        } else {
            $dataResponse = new CourseDto(
                $course->getCode(),
                $course->getTypeFormatString(),
                $course->getPrice(),
            );
            $statusCode = Response::HTTP_OK;
        }

        $response = new JsonResponse();
        // Передаем данные
        $response->setContent($serializer->serialize($dataResponse, 'json'));

        return $response;
    }
    /**
     * @OA\Post(
     *     tags={"Courses"},
     *     path="/api/v1/courses/{code}/pay",
     *     summary="Оплата курса",
     *     description="Оплата курса",
     *     security={
     *         { "Bearer":{} },
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Курс куплен",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="success",
     *                     type="boolean",
     *                 ),
     *                 @OA\Property(
     *                     property="course_type",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="expires_at",
     *                     type="string",
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Данный курс не найден",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="404",
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Данный курс не найден",
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=406,
     *         description="У вас недостаточно средств",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="406",
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="На вашем счету недостаточно средств",
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid JWT Token",
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
     *                     example="Invalid JWT Token",
     *                 ),
     *             ),
     *        )
     *     )
     * )
     * @Route("/{code}/pay", name="course_pay", methods={"POST"})
     */
    public function pay(string $code, PaymentService $paymentService, SerializerInterface $serializer,  CourseRepository $courseRepository): Response
    {
        try {

            $course = $courseRepository->findOneBy(['code' => $code]);

            if (!$course) {
                $dataResponse = [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Данный курс не найден',
                ];
                throw new \Exception($dataResponse['message'], $dataResponse['code']);
            }

            /* @var User $user */
            $user = $this->getUser();
            $transaction = $paymentService->paymentCourses($user, $course);
            $expiresAt = $transaction->getExpiresAt();
            $payDto = new PayDTO(
                true,
                $course->getTypeFormatString(),
                $expiresAt ? $expiresAt->format('Y-m-d T H:i:s') : null
            );

            $response = new JsonResponse();

            // Передаем данные
            $response->setContent($serializer->serialize($payDto, 'json'));
            return $response;
        } catch (\Exception $e) {
            $dataResponse = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

        $response = new JsonResponse();
        // Статус ответа

        $response->setContent($serializer->serialize($dataResponse, 'json'));
        return $response;
    }
    /**
     * @OA\Post(
     *     tags={"Courses"},
     *     path="/api/v1/courses/new",
     *     summary="Создание нового курса",
     *     description="Создание нового курса",
     *     operationId="courses.new",
     *     security={
     *         { "Bearer":{} },
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CourseDTO")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Курс успешно создан",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="201"
     *                 ),
     *                 @OA\Property(
     *                     property="success",
     *                     type="bool",
     *                     example="true"
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Курс с данным кодом уже существует в системе",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="405"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Курс с данным кодом уже существует в системе"
     *                 ),
     *             ),
     *        )
     *     ),
     * )
     * @Route("/new", name="course_new", methods={"POST"})
     */
    public function new(Request $request, CourseRepository $courseRepository): Response
    {

        try {
            $serializer = SerializerBuilder::create()->build();
            $courseDTO = $serializer->deserialize($request->getContent(), CourseDTO::class, 'json');

            // Проверка, существует ли курс с данным кодом уже в системе
            $course = $courseRepository->findOneBy(['code' => $courseDTO->getCode()]);

            if ($course) {
                $dataResponse = [
                    'code' => Response::HTTP_METHOD_NOT_ALLOWED,
                    'message' => 'Курс с данным кодом уже существует в системе',
                ];
                throw new \Exception($dataResponse['message'], $dataResponse['code']);
            }

            $course = Course::fromDtoNew($courseDTO);
            // Добавление course в БД
           $courseRepository->add($course,true);

            $dataResponse = [
                'code' => Response::HTTP_CREATED,
                'success' => true,
            ];
        } catch (\Exception $e) {
            $dataResponse = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
        // Ответ от сервиса по данному методу
        $response = new JsonResponse();
        $response->setContent($serializer->serialize($dataResponse, 'json'));

        return $response;
    }

    /**
     * @OA\Post(
     *     tags={"Courses"},
     *     path="/api/v1/courses/{code}/edit",
     *     summary="Редактирование курса",
     *     description="Редактирование курса",
     *     security={
     *         { "Bearer":{} },
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CourseDTO")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Курс изменен",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="200"
     *                 ),
     *                 @OA\Property(
     *                     property="success",
     *                     type="bool",
     *                     example="true"
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Курс с данным кодом уже существует в системе",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="405"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Курс с данным кодом уже существует в системе"
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Данный курс в системе не найден",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="404"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Данный курс в системе не найден"
     *                 ),
     *             ),
     *        )
     *     )
     * )
     * @Route("/{code}/edit", name="course_edit", methods={"POST"})
     */
    public function edit(string $code, Request $request, CourseRepository $courseRepository): Response
    {
        try {
            $serializer = SerializerBuilder::create()->build();
            $courseDTO = $serializer->deserialize($request->getContent(), CourseDTO::class, 'json');

            // Проверка, существует ли курс с данным кодом вообще в системе
            $course = $courseRepository->findOneBy(['code' => $code]);
            if (!$course) {
                $dataResponse = [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Данный курс в системе не найден',
                ];
                throw new \Exception($dataResponse['message'], $dataResponse['code']);
            }

            // Проверка, существует ли курс с новым кодом
            $courseDuplicate = $courseRepository->findOneBy(['code' => $courseDTO->getCode()]);
            if ($courseDuplicate && $code !== $courseDuplicate->getCode()) {
                $dataResponse = [
                    'code' => Response::HTTP_METHOD_NOT_ALLOWED,
                    'message' => 'Курс с данным кодом уже существует в системе',
                ];
                throw new \Exception($dataResponse['message'], $dataResponse['code']);
            }

            $course->fromDtoEdit($courseDTO);
            $courseRepository->add($course,true);

            $dataResponse = [
                'code' => Response::HTTP_OK,
                'success' => true,
            ];
        } catch (\Exception $e) {
            $dataResponse = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

        // Ответ от сервиса по данному методу
        $response = new JsonResponse();

        $response->setContent($serializer->serialize($dataResponse, 'json'));
        return $response;
    }
}