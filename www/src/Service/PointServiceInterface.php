<?php

namespace App\Service;

interface PointServiceInterface
{
    /**
     * Добавляем бонусные баллы пользователю.
     *
     * @param int $sum
     * @return mixed
     */
    public function addPoint(int $sum);
}