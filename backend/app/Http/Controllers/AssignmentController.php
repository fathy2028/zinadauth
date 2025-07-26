<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseCrudController;
use App\Models\Assignment;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;
class AssignmentController extends BaseCrudController
{
    protected $assignmentRepository;
    public function __construct(AssignmentRepositoryInterface $assignmentRepository)
    {
        parent::__construct();
        $this->assignmentRepository = $assignmentRepository;
    }
    /**
     * Get the model instance for BaseCrudController
     */
    protected function getModel(): Assignment
    {
        return new Assignment();
    }
    
}
