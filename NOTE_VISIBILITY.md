# Note visibility by rank

Leaders can now read and write notes only about members they outrank. The Tracker applies the same rule everywhere notes appear.

## The change

**A leader can see, add, edit, delete or restore a note about a member only if that member's rank is lower than their own.** The rule covers every note type and every place notes are shown.

## Why this helps

- **It follows the chain of command.** Notes record conduct, discipline and personal circumstances. Before, any officer could read notes about peers and about members who outrank them. Now a leader sees notes about the people they are responsible for.
- **It is one rule instead of several.** Access used to depend on role, plus extra restrictions on two special note types. Now rank decides, and those two types keep their existing extra restrictions on top.
- **It is the same on every screen.** The member profile, the trashed notes list, the division notes page and the admin panel all use the same check, so a note hidden in one place is not visible in another.
- **The page only offers what works.** If a leader cannot add a note about someone, the Add note button is hidden. They no longer fill in a form and get an error.

### Example: a Staff Sergeant officer viewing notes about a member who is a...

| Member's rank | See and manage notes |
| --- | --- |
| Private | Yes, lower rank |
| Staff Sergeant | No, same rank |
| Master Sergeant | No, higher rank |

## What stays the same

- Admins, developers and anyone granted the manage-all-notes permission still see everything.
- Authors keep read access to notes they wrote, even after the subject is promoted past them.
- The existing extra restrictions on senior leader and Master Sergeant note types still apply.
- No notes are deleted. Notes a leader can no longer reach are hidden from them, not removed.

## Decisions and trade-offs

- **Applies to old notes.** The rule is retroactive. A leader who could read a note last week about a peer or a higher-ranked member will not see it after this ships.
- **Current rank counts.** Access follows each member's rank today. When someone is promoted, the set of leaders who can see their notes changes with it.
- **No unit exception.** A platoon or squad leader does not get access to notes about a higher-ranked member of their own unit. An exception can be added later if leadership wants one.

## Rollout

The change ships as its own pull request, separate from the other authorization work in progress. It can be applied to the dev environment first and promoted to production when leadership decides to proceed.
