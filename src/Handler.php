<?php
namespace GitHubWebhook;

class Handler
{
    private $secret;
    private $remote;
    private $gitDir;
    private $data;
    private $event;
    private $delivery;
    private $gitOutput;
    private $gitExitCode;

    public function __construct($secret, $gitDir, $remote = null)
    {
        $this->secret = $secret;
        $this->remote = $remote;
        $this->gitDir = $gitDir;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getDelivery()
    {
        return $this->delivery;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getGitDir()
    {
        return $this->gitDir;
    }

    public function getGitOutput()
    {
        return $this->gitOutput;
    }

    public function getRemote()
    {
        return $this->remote;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function getGitExitCode()
    {
        return $this->gitExitCode;
    }

    public function handle()
    {
        if (!$this->validate()) {
            return false;
        }

        return true;
    }

    public function validate()
    {
        $signature = @$_SERVER['HTTP_X_HUB_SIGNATURE_256'];
        $event = @$_SERVER['HTTP_X_GITHUB_EVENT'];
        $delivery = @$_SERVER['HTTP_X_GITHUB_DELIVERY'];

        if (!isset($signature, $event, $delivery)) {
            return false;
        }

        $payload = file_get_contents('php://input');

        if (!$this->validateSignature($signature, $payload)) {
            return false;
        }

        $this->data = json_decode($_POST['payload'] ?? '{}',true);
        $this->event = $event;
        $this->delivery = $delivery;
        return true;
    }

    protected function validateSignature($gitHubSignatureHeader, $payload)
    {
        list ($algo, $gitHubSignature) = explode("=", $gitHubSignatureHeader);

        if ($algo !== 'sha256') {
            // see https://developer.github.com/webhooks/securing/
            return false;
        }

        $payloadHash = hash_hmac($algo, $payload, $this->secret);
        return ($payloadHash === $gitHubSignature);
    }
}
