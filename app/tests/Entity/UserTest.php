<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User();
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setEmail('jean@example.com');

        $this->assertEquals('Jean', $user->getFirstName());
        $this->assertEquals('Dupont', $user->getLastName());
        $this->assertEquals('jean@example.com', $user->getEmail());
    }

    public function testGetFullName(): void
    {
        $user = new User();
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');

        $this->assertEquals('Jean Dupont', $user->getFullName());
    }

    public function testDefaultRoles(): void
    {
        $user = new User();
        
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }

    public function testCreatedAtIsSetAutomatically(): void
    {
        $user = new User();
        
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt());
    }
}
