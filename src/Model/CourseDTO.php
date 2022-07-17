<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="CourseDTO",
 *     description="CourseDTO"
 * )
 * Class CourseDTO
 * @package App\Model
 */
class CourseDTO
{
    /**
     * @OA\Property(
     *     format="string",
     *     title="code",
     *     description="Код курса",
     *     example="MLSADKLD13213KSDMDNVM35"
     * )
     * @Serializer\Type("string")
     */
    private $code;

    /**
     * @OA\Property(
     *     format="string",
     *     title="type",
     *     description="Тип курса",
     *     example="buy"
     * )
     * @Serializer\Type("string")
     */
    private $type;

    /**
     * @OA\Property(
     *     format="float",
     *     title="price",
     *     description="Стоимость курса",
     *     example="15000"
     * )
     * @Serializer\Type("float")
     */
    private $price;
    /**
     * @OA\Property(
     *     format="string",
     *     title="title",
     *     description="Название курса",
     *     example="Основы рынка"
     * )
     * @Serializer\Type("string")
     */
    private $title;


    public function __construct(string $code, string $type, float $price)
    {
        $this->code = $code;
        $this->type = $type;
        $this->price = $price;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

}
