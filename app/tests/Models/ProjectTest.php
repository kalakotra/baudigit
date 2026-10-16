<?php

namespace app\Tests\Models;

use app\Models\Project;
use app\Models\ProjectMember;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

class ProjectTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testGetAssignedMembers()
    {
        // Create a project
        $project = Project::create();
        $project->Title = 'Test Project';
        $project->write();

        // Create an assigned member
        $assignedMember1 = Member::create();
        $assignedMember1->FirstName = 'Assigned';
        $assignedMember1->Surname = 'One';
        $assignedMember1->Email = 'assigned1@example.com';
        $assignedMember1->write();

        $assignedMember2 = Member::create();
        $assignedMember2->FirstName = 'Assigned';
        $assignedMember2->Surname = 'Two';
        $assignedMember2->Email = 'assigned2@example.com';
        $assignedMember2->write();

        // Create an unassigned member
        $unassignedMember = Member::create();
        $unassignedMember->FirstName = 'Unassigned';
        $unassignedMember->Surname = 'One';
        $unassignedMember->Email = 'unassigned@example.com';
        $unassignedMember->write();

        // Assign members to project
        $projectMember1 = ProjectMember::create();
        $projectMember1->ProjectID = $project->ID;
        $projectMember1->MemberID = $assignedMember1->ID;
        $projectMember1->write();

        $projectMember2 = ProjectMember::create();
        $projectMember2->ProjectID = $project->ID;
        $projectMember2->MemberID = $assignedMember2->ID;
        $projectMember2->write();

        // Test the getAssignedMembers method
        $assignedMembers = $project->getAssignedMembers();

        $this->assertNotNull($assignedMembers, 'Assigned members should not be null');
        $this->assertEquals(2, $assignedMembers->count(), 'There should be 2 assigned members');

        $assignedMemberIDs = $assignedMembers->column('ID');
        $this->assertContains($assignedMember1->ID, $assignedMemberIDs, 'Assigned member 1 should be in the list');
        $this->assertContains($assignedMember2->ID, $assignedMemberIDs, 'Assigned member 2 should be in the list');
        $this->assertNotContains($unassignedMember->ID, $assignedMemberIDs, 'Unassigned member should not be in the list');
    }
}
