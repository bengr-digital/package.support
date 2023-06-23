<?php

namespace Bengr\Support\Http;

use Illuminate\Contracts\Support\Responsable;

class Response implements Responsable
{
    protected $content;

    protected array $json = [];

    protected array $headers = [];

    protected ?string $view_name = null;

    protected array $view_data = [];

    protected int $status = 200;

    protected ?string $exception = null;

    protected ?string $resource_class = null;

    protected $resource_data = null;

    protected bool $resource_is_collection = false;

    protected $redirect = null;

    final public function __construct($content = '')
    {
        $this->content = $content;
    }

    public static function make($content = ''): static
    {
        return app(static::class, ['content' => $content]);
    }

    public function toResponse($request)
    {
        if ($this->resource_class) {
            if ($this->resource_is_collection) return $this->resource_class::collection($this->resource_data);

            return $this->resource_class::make($this->resource_data);
        }

        if ($this->exception) {
            throw new $this->exception();
        }

        if ($request->wantsJson() && $this->json !== []) {
            return response()->json($this->json, $this->status, $this->headers);
        }

        if ($this->view_name) {
            return view($this->view_name, $this->view_data);
        }

        if ($this->redirect) {
            return redirect($this->redirect);
        }

        return response($this->content, $this->status, $this->headers);
    }

    public function view(string $name, ?array $data = []): self
    {
        $this->view_name = $name;
        $this->view_data = $data;

        return $this;
    }

    public function status(int $code): self
    {
        $this->status = $code;

        return $this;
    }

    public function json(array $json): self
    {
        $this->json = $json;

        return $this;
    }

    public function headers(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function throw(string $exception): self
    {
        $this->exception = $exception;

        return $this;
    }

    public function resource(string $class, $data): self
    {
        $this->resource_class = $class;
        $this->resource_data = $data;
        $this->resource_is_collection = false;

        return $this;
    }

    public function resources(string $class, $data): self
    {
        $this->resource_class = $class;
        $this->resource_data = $data;
        $this->resource_is_collection = true;

        return $this;
    }

    public function redirect($redirect): self
    {
        $this->redirect = $redirect;

        return $this;
    }
}
