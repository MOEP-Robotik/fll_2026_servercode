<?php
namespace Models;

class SentInfo {
    public bool $confirmation;
    public bool $lvr;

    public function __construct(bool $confirmation = false, bool $lvr = false) {
        $this->confirmation = $confirmation;
        $this->lvr = $lvr;
    }

    public function toJSON(): string {
        return json_encode([
            'confirmation' => $this->confirmation,
            'lvr' => $this->lvr
        ]);
    }
}