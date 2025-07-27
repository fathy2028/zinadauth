<?php

namespace App\Repositories\Eloquent;

use App\Models\Assignment;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;

class AssignmentRepository extends BaseRepository implements AssignmentRepositoryInterface {
    public function __construct()
    {
        parent::__construct(new Assignment());
    }
}
