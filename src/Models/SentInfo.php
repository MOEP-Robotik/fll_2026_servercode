<?php
namespace Models;

class SentInfo {
    public bool | null $confirmation;
    public bool | null $lvr = null;

    public function __construct(bool | null $confirmation = null, bool | null $lvr = null) {
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