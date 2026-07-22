<?php
/**
 * Crudable.php
 * OOP CONCEPT DEMONSTRATED: Abstraction (via Interface)
 * Any class that "implements Crudable" is GUARANTEED to provide these
 * four methods, even though each class implements them differently
 * (this also sets up Polymorphism when we call them interchangeably).
 */
interface Crudable
{
    public function create();
    public function update(): bool;
    public function delete(): bool;
    public static function findById(int $id): ?array;
}
