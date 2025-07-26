<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseCrudController;
use App\Models\Assignment;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;
use App\Http\Requests\AssignmentRequest;

class AssignmentController extends BaseCrudController
{
    protected $assignmentRepository;
    public function __construct(AssignmentRepositoryInterface $assignmentRepository)
    {
        parent::__construct();
        $this->assignmentRepository = $assignmentRepository;
    }

    protected function getStoreFormRequestClass(): string
    {
        return AssignmentRequest::class;
    }

    protected function getUpdateFormRequestClass(): string
    {
        return AssignmentRequest::class;
    }
    /**
     * Get the model instance for BaseCrudController
     */
    protected function getModel(): Assignment
    {
        return new Assignment();
    }

    public function createAssignment()
    {
        return $this->store();
    }

    public function getAssignments()
    {
        return $this->index();
    }

    public function updateAssignment($id)
    {
        return $this->update($id);
    }

    public function deleteAssignment($id)
    {
        return $this->destroy($id);
    }
}
