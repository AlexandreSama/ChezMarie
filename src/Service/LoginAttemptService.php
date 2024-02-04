<?php
// src/Service/LoginAttemptService.php

namespace App\Service;

use Psr\Cache\CacheItemInterface as CacheCacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Security\Core\Exception\LockedException;

class LoginAttemptService
{
    private const MAX_ATTEMPTS = 5;
    private const BLOCK_TIME = 20; // 5 minutes en secondes

    private $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
    }
    public function addLoginAttempt(string $username)
    {
        $cacheKey = 'login_attempts_' . md5($username);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            // Si l'item existe, incrémente le nombre d'essais
            $attempts = $cacheItem->get() + 1;
        } else {
            // Sinon, initialise à 1 pour le premier essai
            $attempts = 1;
        }

        if ($attempts >= self::MAX_ATTEMPTS) {
            $timeKey = 'login_time_' . md5($username);
            $timeItem = $this->cache->getItem($timeKey);
            $timeItem->set(time());
            $currentTime = time();
            $timeItem->expiresAfter(self::BLOCK_TIME); // Assurez-vous que le timestamp expire après BLOCK_TIME
            $this->cache->save($timeItem);
        }

        // Mettez à jour l'item de cache et sauvegardez-le
        $cacheItem->set($attempts);
        $cacheItem->expiresAfter(self::BLOCK_TIME);
        $this->cache->save($cacheItem);
    }

    public function checkLoginAttempt(string $username)
    {
        $cacheKey = 'login_attempts_' . md5($username);

        // Récupérez l'item de cache
        $cacheItem = $this->cache->getItem($cacheKey);

        // Vérifiez si l'item est déjà dans le cache
        if ($cacheItem->isHit()) {
            // Récupérez le nombre d'essais actuel si l'item existe
            $attempts = $cacheItem->get();
        } else {
            // Sinon, initialisez le nombre d'essais à 0
            $attempts = 0;
        }

        if ($attempts >= self::MAX_ATTEMPTS) {
            $blockTimeRemaining = $this->getBlockTimeRemaining($username);
            if ($blockTimeRemaining > 0) {
                throw new LockedException('Trop d\'essais infructueux, veuillez réessayer dans ' . $blockTimeRemaining . ' secondes.');
            }
        }
    }

    public function getBlockTimeRemaining(string $username): int
    {
        $cacheKey = 'login_attempts_' . md5($username);
        $timeKey = 'login_time_' . md5($username);
    
        // Récupérez l'item de cache pour le nombre de tentatives
        $attemptsItem = $this->cache->getItem($cacheKey);
        $attempts = $attemptsItem->isHit() ? $attemptsItem->get() : 0;
    
        if ($attempts >= self::MAX_ATTEMPTS) {
            // Récupérez l'item de cache pour le timestamp du dernier essai
            $timeItem = $this->cache->getItem($timeKey);
            $lastAttemptTime = $timeItem->isHit() ? $timeItem->get() : 0;

            // Si le timestamp n'est pas défini, considérez que le temps de blocage est terminé
            if ($lastAttemptTime === 0) {
                return 0;
            }
    
            // Calculer le temps restant
            $timeRemaining = max(self::BLOCK_TIME - (time() - $lastAttemptTime), 0);
            return $timeRemaining;
        }
    
        return 0;
    }
}
