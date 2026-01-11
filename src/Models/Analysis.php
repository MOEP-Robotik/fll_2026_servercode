<?php
/*
Analysis (DB):
    id: int (von DB generiert)
    submission_id: int
    material: text
    confidence: double
    notes: text
    created_at: timestamp (von DB generiert)
*/
class Analysis{
    public int $submission_id;
    public string $material;
    public float $confidence;
    public string $notes;
}