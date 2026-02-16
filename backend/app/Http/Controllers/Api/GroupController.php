<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * List all groups
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Only admin can view all groups
        if (!$user->isAdmin()) {
            // Non-admin users can only see their own groups
            $groups = $user->groups()->paginate($request->input('per_page', 15));
        } else {
            $groups = Group::with('members')->paginate($request->input('per_page', 15));
        }

        return response()->json($groups);
    }

    /**
     * Create a new group
     */
    public function store(Request $request)
    {
        // Only admin can create groups
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:groups,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::log(auth()->user(), 'CREATE', $group);

        return response()->json($group, 201);
    }

    /**
     * Show a specific group with members
     */
    public function show(Group $group)
    {
        $user = auth()->user();

        // Only group members and admin can view group details
        if (!$user->isAdmin() && !$group->hasMember($user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $group->load(['members', 'folderPermissions', 'documentPermissions']);
        AuditLogger::log($user, 'VIEW', $group);

        return response()->json($group);
    }

    /**
     * Update a group
     */
    public function update(Request $request, Group $group)
    {
        // Only admin can update groups
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255|unique:groups,name,' . $group->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $group->update($request->only('name', 'description'));
        AuditLogger::log(auth()->user(), 'UPDATE_METADATA', $group);

        return response()->json($group);
    }

    /**
     * Delete a group (soft delete)
     */
    public function destroy(Group $group)
    {
        // Only admin can delete groups
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $group->delete();
        AuditLogger::log(auth()->user(), 'SOFT_DELETE', $group);

        return response()->noContent();
    }

    /**
     * Add a member to the group
     */
    public function addMember(Request $request, Group $group)
    {
        $user = auth()->user();

        // Only admin can add members
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        $memberUser = User::find($request->user_id);

        // Check if already a member
        if ($group->hasMember($memberUser)) {
            return response()->json(['error' => 'User is already a member'], 422);
        }

        $group->addMember($memberUser);
        AuditLogger::log($user, 'UPDATE_METADATA', $group, ['action' => 'added_member', 'user_id' => $request->user_id]);

        return response()->json(['message' => 'Member added successfully'], 201);
    }

    /**
     * Remove a member from the group
     */
    public function removeMember(Request $request, Group $group)
    {
        $user = auth()->user();

        // Only admin can remove members
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        $memberUser = User::find($request->user_id);

        if (!$group->hasMember($memberUser)) {
            return response()->json(['error' => 'User is not a member'], 422);
        }

        $group->removeMember($memberUser);
        AuditLogger::log($user, 'UPDATE_METADATA', $group, ['action' => 'removed_member', 'user_id' => $request->user_id]);

        return response()->noContent();
    }

    /**
     * List group members
     */
    public function members(Group $group, Request $request)
    {
        $user = auth()->user();

        // Only group members and admin can view members
        if (!$user->isAdmin() && !$group->hasMember($user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $members = $group->members()
            ->paginate($request->input('per_page', 15));

        return response()->json($members);
    }
}
