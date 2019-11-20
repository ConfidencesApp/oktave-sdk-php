<?php

namespace Oktave;

class Response
{

    /**
     * @var int the http status code
     */
    private $statusCode;

    /**
     * @var string the oktave request identifier
     */
    private $requestID;

    /**
     * @var float the time taken to execute the call
     */
    private $executionTime;

    /**
     * @var string the resource name
     */
    private $resource = 'data';

    /**
     * @var object the json decoded response in full
     */
    private $raw;

    /**
     * @var array|object the responses data
     */
    private $data;

    /**
     * @var object the responses meta
     */
    private $meta;

    /**
     * @var array the responses root level links
     */
    private $links = [];

    /**
     * @var array the responses errors
     */
    private $errors = [];

    /**
     *  Get the raw json decoded response
     *
     * @return object
     */
    public function getRaw(): ?object
    {
        return $this->raw;
    }

    /**
     *  Parse $this->raw and set on objects
     */
    public function parse(): self
    {
        $dataRoot = str_replace('-', '_', $this->resource);

        if (isset($this->raw->{$dataRoot})) {
            $this->setData($this->raw->{$dataRoot});
        }

        if (isset($this->raw->links)) {
            $this->setLinks($this->raw->links);
        }

        if (isset($this->raw->meta)) {
            $this->setMeta($this->raw->meta);
        }

        if (isset($this->raw->errors)) {
            $this->setErrors($this->raw->errors);
        }

        return $this;
    }

    /**
     *  Get the response data
     *
     * @return object|array
     */
    public function data()
    {
        return $this->data;
    }

    /**
     *  Get the response errors
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     *  Get the response meta
     *
     * @return null|object
     */
    public function meta()
    {
        return $this->meta;
    }

    /**
     *  Get the response links
     *
     * @return array
     */
    public function links()
    {
        return $this->links;
    }

    /**
     *  Get the request ID
     *
     * @return string
     */
    public function getRequestID(): ?string
    {
        return $this->requestID;
    }

    /**
     *  Get the status code
     *
     * @return int
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     *  Get the execution time (including network latency)
     *
     * @return float
     */
    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    /**
     *  Set the status code
     *
     * @param  int
     *
     * @return $this
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     *  Set the oktave request ID
     *
     * @param  string
     *
     * @return $this
     */
    public function setRequestID(string $id): self
    {
        $this->requestID = $id;
        return $this;
    }

    /**
     *  Set the execution time
     *
     * @param  float
     *
     * @return $this
     */
    public function setExecutionTime(float $time): self
    {
        $this->executionTime = $time;
        return $this;
    }

    /**
     *  Set the raw response
     *
     * @param  object
     *
     * @return $this
     */
    public function setRaw(object $raw): self
    {
        $this->raw = $raw;
        return $this;
    }

    /**
     *  Set the resource concerned by the response
     *
     * @param  string|null  $resource
     *
     * @return $this
     */
    public function setResource(string $resource = null): self
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     *  Set the response data
     *
     * @param  array|object
     *
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     *  Set the response meta
     *
     * @param  object
     *
     * @return $this
     */
    public function setMeta(object $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     *  Set the response links
     *
     * @param  object|array
     *
     * @return $this
     */
    public function setLinks($links = null): self
    {
        $this->links = is_array($links) ? $links : (is_object($links) ? json_decode(json_encode($links), true) : []);
        return $this;
    }

    /**
     *  Set the response errors
     *
     * @param  array
     *
     * @return $this
     */
    public function setErrors(array $errors = []): self
    {
        $this->errors = $errors;
        return $this;
    }

}
