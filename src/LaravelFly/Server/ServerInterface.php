<?php

namespace LaravelFly\Server;

interface ServerInterface
{
    public function config(array $options);

    public function create();

    public function setListeners();

    public function start();

    public function path($path = null);

}