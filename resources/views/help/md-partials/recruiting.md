# Recruiting New Members

Recruitment adds a new member to the Tracker and performs all associated setup — forum rank and division
assignment, Discord sync, activity recording, and member request creation. The process varies slightly
depending on whether the recruit already has a forum account or is coming through the Discord pending
registration flow.

**Who can recruit:** Officers, Senior Leaders, and Admins.

---

## Two Recruitment Paths

### Path 1 — Forum Member

Use this path when the recruit already has a forum account.

1. Enter their **forum member ID** (the numeric ID from their forum profile).
2. The Tracker validates the ID against the AOD forums and checks their forum group.
3. If valid, the forum username auto-fills the recruit details form.
4. Complete the remaining steps — in-game handle, rank, platoon/squad assignment, agreements, and tasks.
5. Submit. The member is created immediately.

### Path 2 — Pending Discord Registration

Use this path when the recruit signed in via Discord but has not yet been recruited.

1. Select the recruit from the **Pending Discord Registrations** dropdown.
2. The form pre-fills with the information they provided during Discord sign-in.
3. Complete the remaining steps as above.
4. Submit. The Tracker queues a job to create or link the forum account and then creates the member record.

If the pending user's forum account already exists (looked up by email), it is linked automatically.
If it does not exist and they provided a date of birth during registration, a forum account is created for them.

---

## Member Verification Details

When a forum member ID is validated, the Tracker returns several pieces of information:

| Field | Meaning |
|---|---|
| **Forum group** | Must be `Registered Users` or `Awaiting Moderation` to be eligible |
| **Previous Member badge** | The member ID already exists in the Tracker — this is a re-recruitment |
| **Discord match notice** | A tracker member with the same Discord ID was found — may indicate a duplicate account |

### Discord ID Matching

When a Discord ID match is found, the notice shows the matched member's name and division. This does not
block recruitment but is a signal to investigate before proceeding — the recruit may be re-joining under a
new forum account, or there may be a duplicate registration.

---

## Common Errors

### "Member is not in the Registered Users group"

The forum account exists but is not in an eligible group. Specific reasons:

| Forum group | Reason shown |
|---|---|
| Awaiting Email Confirmation | User has not verified their forum email |
| Banned | User's forum account is banned |
| Already a member | User is already an AOD member on the forums |
| Other staff groups | Member is already staff rank or above |

**Resolution:** Ask the recruit to verify their email, or contact an admin if the account appears incorrectly grouped.

### "User not found" / invalid ID

The forum member ID does not correspond to an active account. Double-check the ID from the recruit's forum
profile URL (`https://clanaod.net/forums/member.php?u=XXXXX` — the number after `u=` is their ID).

### Forum account not found for Discord user

Applies to the Discord path only. Occurs when:

- No forum account matches the email they registered with, **and**
- No date of birth was provided (so a new account cannot be created automatically).

**Resolution:** Have the recruit re-register through Discord and ensure they provide a date of birth. 

### Forum account creation failed (Discord path)

The automatic forum account creation step failed. This is logged in the recruiting log channel.

**Resolution:** Report this to an admin, as there may be an issue preventing account creation. 

### Discord info could not be set on forum profile

After creating or finding the forum account, the Tracker attempts to link the recruit's Discord ID to
their forum profile. If the stored procedure reports zero rows matched, recruitment is aborted.

**Resolution:** Contact an admin to investigate the forum database record.

### Division is shut down

The selected division is marked as shut down and cannot receive new members. The form page redirects
back with an error.

---

## Re-Recruiting a Previous Member

If a recruit's forum ID already exists in the Tracker (shown as **Previous Member**), the recruitment
will overwrite the existing record's activity and assignment. This is expected for returning members.

Review any existing tags, division history, and notes on the member's profile before proceeding — these
are retained.

---

## After Recruitment Completes

Once submitted successfully, the Tracker performs these actions automatically:

- Creates the member record with the assigned rank, platoon, and squad
- Records an initial activity and rank action
- Creates a member request for the division's review queue
- Dispatches a Discord sync job to update the member's Discord roles
- Sends a notification to the division (or the external recruit channel if recruiting into a different division than your own)
