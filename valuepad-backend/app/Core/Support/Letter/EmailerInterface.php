<?php
namespace ValuePad\Core\Support\Letter;

interface EmailerInterface
{
	public function send(Email $email);
}
