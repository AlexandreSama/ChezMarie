<?php
// src/Service/LoginAttemptService.php

namespace App\Service;

use Psr\Cache\CacheItemInterface as CacheCacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\CacheItemInterface;

class LoginAttemptService
{
    private const MAX_ATTEMPTS = 5;
    private const BLOCK_TIME = 300; // 5 minutes en secondes

    private $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
    }

    public function addLoginAttempt(string $username)
    {
        $cacheKey = 'login_attempts_' . md5($username); // Utilisez md5 pour créer une clé hachée

        $this->cache->get($cacheKey, function (CacheCacheItemInterface $item) {
            $item->expiresAfter(self::BLOCK_TIME); // Utilisez expiresAfter() pour définir l'expiration en secondes
            $attempts = $item->get() + 1;
            $item->set($attempts);
        });
    }

    public function checkLoginAttempt(string $username)
    {
        $cacheKey = 'login_attempts_' . md5($username); // Utilisez md5 pour créer une clé hachée

        $attempts = $this->cache->get($cacheKey, function (CacheCacheItemInterface $item) {
            $item->expiresAfter(self::BLOCK_TIME); // Utilisez expiresAfter() pour définir l'expiration en secondes
            return 0;
        });

        if ($attempts >= self::MAX_ATTEMPTS) {
            $blockTimeRemaining = $this->getBlockTimeRemaining($username);
            if ($blockTimeRemaining > 0) {
                throw new LockedException('Trop d\'essais infructueux, veuillez réessayer dans ' . $blockTimeRemaining . ' secondes.');
            }
        }
    }

    private function getBlockTimeRemaining(string $username): int
    {
        $cacheKey = 'login_attempts_' . md5($username); // Utilisez md5 pour créer une clé hachée

        $attempts = $this->cache->get($cacheKey, function (CacheCacheItemInterface $item) {
            $item->expiresAfter(self::BLOCK_TIME); // Utilisez expiresAfter() pour définir l'expiration en secondes
            return 0;
        });

        if ($attempts >= self::MAX_ATTEMPTS) {
            return self::BLOCK_TIME;
        }

        return 0;
    }
}