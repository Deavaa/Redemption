<?php

namespace App\Session;

use SessionHandlerInterface;

/**
 * NoGarbageSessionHandler
 *
 * This is the NUCLEAR OPTION for fixing session expiration on XAMPP.
 *
 * The problem: PHP's native session garbage collection (gc) deletes
 * session files/rows even when the user is actively using the page.
 * On XAMPP, session.gc_maxlifetime is often 300 seconds (5 min).
 * Even setting gc_probability=0 via ini_set doesn't always work
 * because PHP may have already started the session before our
 * ini_set runs, or the ini_set may be ignored by the SAPI.
 *
 * The solution: Wrap ANY session handler so that the gc() method
 * does NOTHING. When PHP calls gc() (garbage collection), it
 * returns 0 and does not delete any sessions. Period.
 *
 * This is the ONLY approach that is 100% immune to:
 * - PHP's native session.gc_maxlifetime
 * - PHP's native session.gc_probability
 * - .user.ini not being read
 * - .htaccess php_value being ignored
 * - ini_set() being called too late
 * - Config cache being stale
 * - Any PHP configuration issue whatsoever
 *
 * Sessions will still eventually expire based on Laravel's
 * session.lifetime (480 min = 8 hours), but ONLY through
 * Laravel's own expiry check, NOT through garbage collection.
 */
class NoGarbageSessionHandler implements SessionHandlerInterface
{
    protected SessionHandlerInterface $handler;

    public function __construct(SessionHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function open(string $path, string $name): bool
    {
        return $this->handler->open($path, $name);
    }

    public function close(): bool
    {
        return $this->handler->close();
    }

    public function read(string $id): string|false
    {
        return $this->handler->read($id);
    }

    public function write(string $id, string $data): bool
    {
        return $this->handler->write($id, $data);
    }

    public function destroy(string $id): bool
    {
        return $this->handler->destroy($id);
    }

    /**
     * GARBAGE COLLECTION — DISABLED.
     *
     * This is the key method. No matter what PHP's gc_maxlifetime
     * or gc_probability settings are, this method will NEVER delete
     * any sessions. It always returns 0 (no sessions deleted).
     *
     * Laravel's session expiry still works because Laravel checks
     * the session's last_activity timestamp independently of gc().
     */
    public function gc(int $max_lifetime): int|false
    {
        // DO NOTHING. Never delete sessions via garbage collection.
        // Laravel handles session expiry through its own mechanism
        // (checking last_activity against session.lifetime).
        return 0;
    }

    /**
     * Get the underlying handler (for debugging).
     */
    public function getHandler(): SessionHandlerInterface
    {
        return $this->handler;
    }
}
