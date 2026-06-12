<?php

namespace Quickweb\System\Support;

use Illuminate\Filesystem\Filesystem;
use Quickweb\System\Contracts\StateStoreInterface;

class FileStateStore implements StateStoreInterface
{
  /** @var Filesystem */
  private $files;

  /** @var string */
  private $path;

  public function __construct(Filesystem $files, string $path)
  {
    $this->files = $files;
    $this->path = $path;
  }

  public function get(): array
  {
    if (!$this->files->exists($this->path)) {
      return [];
    }

    $contents = $this->files->get($this->path);
    $decoded = json_decode($contents, true);

    return is_array($decoded) ? $decoded : [];
  }

  public function save(array $state): void
  {
    $directory = dirname($this->path);

    if (!$this->files->isDirectory($directory)) {
      $this->files->makeDirectory($directory, 0755, true);
    }

    $this->files->put(
      $this->path,
      json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
  }

  public function isInitialized(): bool
  {
    $state = $this->get();

    return !empty($state['initialized_at']);
  }

  public function getLastSentAt(): ?\DateTimeInterface
  {
    $state = $this->get();

    if (empty($state['last_sent_at'])) {
      return null;
    }

    try {
      return new \DateTimeImmutable($state['last_sent_at']);
    } catch (\Exception $e) {
      return null;
    }
  }
}
