<?php
namespace App\Contracts;

interface Authenticatable
{
  public function getId(): int;
  public function getName(): string;
  public function getEmail(): string;
}