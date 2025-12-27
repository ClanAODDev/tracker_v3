<?php

namespace Database\Seeders;

use App\Models\TrainingModule;
use Illuminate\Database\Seeder;

class TrainingContentSeeder extends Seeder
{
    public function run(): void
    {
        $sgtModule = TrainingModule::create([
            'name' => 'SGT Training Process',
            'slug' => 'sgt',
            'description' => 'Training module for newly promoted Sergeants covering duties, structure, forum moderation, Discord, and miscellaneous information.',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $this->createSgtSections($sgtModule);

        $divisionModule = TrainingModule::create([
            'name' => 'Division Checklist',
            'slug' => 'division-checklist',
            'description' => 'Step-by-step guide for creating or removing divisions, covering DNS, forums, Discord, and Tracker configuration.',
            'display_order' => 2,
            'is_active' => true,
            'show_completion_form' => false,
            'checkpoint_label' => 'Tasks',
        ]);

        $this->createDivisionChecklistSections($divisionModule);
    }

    private function createSgtSections(TrainingModule $module): void
    {
        $sections = [
            [
                'title' => 'SGT Duties',
                'icon' => 'fa-tasks',
                'display_order' => 1,
                'content' => $this->getSgtDutiesContent(),
                'checkpoints' => [
                    'Explained responsibility scope beyond division',
                    'Reviewed SGT Decision Log usage',
                    'Discussed "stay in your lane" philosophy',
                    'Covered CO authority in divisions',
                ],
            ],
            [
                'title' => 'SGT Structure',
                'icon' => 'fa-sitemap',
                'display_order' => 2,
                'content' => $this->getSgtStructureContent(),
                'checkpoints' => [
                    'Reviewed leadership structure page',
                    'Explained SSgt role and responsibilities',
                    'Discussed who to contact for questions',
                    'Planned follow-up training check-in',
                ],
            ],
            [
                'title' => 'Forum Moderation',
                'icon' => 'fa-comments',
                'display_order' => 3,
                'content' => $this->getForumModContent(),
                'checkpoints' => [
                    'Demonstrated user search feature',
                    'Warned about Ban User button placement',
                    'Explained IP address search use-cases',
                    'Covered Edit AOD info for promotions/demotions',
                    'Discussed ban procedures and reversal',
                    'Reviewed past bans format',
                    'Demonstrated award assignment process',
                ],
            ],
            [
                'title' => 'Discord Moderation',
                'icon' => 'fa-discord fab',
                'display_order' => 4,
                'content' => $this->getDiscordModContent(),
                'checkpoints' => [
                    'Showed Sgts Discord Channel',
                    'Reviewed admin commands (/ and ! commands)',
                    'Demonstrated kick and ban options',
                    'Explained channel creation process',
                    'Covered voice channel member movement',
                ],
            ],
            [
                'title' => 'Misc Information',
                'icon' => 'fa-info-circle',
                'display_order' => 5,
                'content' => $this->getMiscContent(),
                'checkpoints' => [
                    'Explained post view tracking feature',
                    'Discussed cross-division forum access',
                    'Reminded about guest etiquette',
                    'Encouraged idea sharing with credit',
                    'Conducted steak cooking inquiry',
                    'Arranged follow-up check-in time',
                ],
            ],
        ];

        foreach ($sections as $sectionData) {
            $checkpoints = $sectionData['checkpoints'];
            unset($sectionData['checkpoints']);

            $section = $module->sections()->create($sectionData);

            foreach ($checkpoints as $order => $label) {
                $section->checkpoints()->create([
                    'label' => $label,
                    'display_order' => $order + 1,
                ]);
            }
        }
    }

    private function getSgtDutiesContent(): string
    {
        return <<<'MD'
#### Duties and Responsibilities

##### Explain the differences in responsibilities between a Cpl and a Sgt in AOD

* Understand that your area of responsibility as a SGT now expands beyond that of your division. Members from other divisions may come to you for assistance when their leadership is not available.
* Take care not to step on any toes or make decisions that depart from that division's norms. Utilize the [SGT Decision Log](https://www.clanaod.net/forums/showthread.php?t=79087) when forced to take action, and document fully.
* Stay in your Lane: Strive to be helpful but don't meddle or otherwise interfere.
* COs in any division are the final say in any matters pertaining to their divisions. Know this, Live this and Love this.
MD;
    }

    private function getSgtStructureContent(): string
    {
        return <<<'MD'
#### SGT Structure & SSgts Duties

##### Review both the Sgt structure and the SSgt lists below.

Reference the [leadership structure](https://tracker.clanaod.net/clan/leadership) for guidance on who is a Sgt in AOD and what their role is.

##### Explain briefly what a Staff Sergeant's role is:

* Staff Sergeants (SSgts) are responsible for the onboarding and training of all newly promoted Sgts.
* SSgts moderate the Division Request Forums in conjunction with prospective division leaders (usually other Sgts). SSgts should step in if a division request goes off the rails or lies dormant for an excessive period of time, or if the request has little hope of being an actual division within AOD.
* SSgts are experienced Sgts who typically will hold CO or XO tags in their division. They are the first stop for any Sgt who has questions about how to perform their tasks.
* Your trainee Sgt should come away from this conversation with an understanding that they are part of a highly organized structure, and that there is always someone there to help if they get stuck. Take ownership of their development and offer to help them if they have any questions.

**Be sure to plan time in the coming days and weeks to check on their progress.**
MD;
    }

    private function getForumModContent(): string
    {
        return <<<'MD'
#### Forum Moderation

##### Review Basic Forum Moderation Functions

* Go over Search for users: Remind them of the poor placement of the `Ban User` button and to be careful.
* Search IP addresses: Give example use-cases for this feature.
* Explain why/how to Edit AOD info (promotions, demotions).

##### Review the Ban User feature, Do not gloss over this.

* Banning is never an emotional response. It is a tool to protect AOD from threats that could hinder the clan.
* Make sure they understand that they need to remove AOD membership status before banning.
* Make sure the ban is permanent and that a reason is given for the ban.
* Discuss reversing a ban, and the do's and don't. When in doubt seek higher authority.
* Review [past bans in the Sgt section](https://www.clanaod.net/forums/forumdisplay.php?f=339) to ensure that they know the correct format.

#### Review award section and how to assign an award.

* Review the process on how to assign an award. Try testing by having them grant and revoke an award for you.
MD;
    }

    private function getDiscordModContent(): string
    {
        return <<<'MD'
#### Discord

* Show them the Sgts Discord Channel - make sure they understand that important information from Msgts+ will be added here and they should be checking this channel regularly.
* Admin Commands - Sgts can mute, kick and ban members in Discord. Please have them review these commands (`/` commands & `!` commands) - have them right click on a member in Discord and show them the KICK & BAN options.
* Adding Channels - Make sure they know to ask their CO/XO team if a member wants a new Discord channel created. Division COs should always know.
* Moving Members in Voice Channels - Explain to them that Sgts+ can move people in our Discord Voice Channels just like TeamSpeak.
MD;
    }

    private function getMiscContent(): string
    {
        return <<<'MD'
#### Misc Information

* Point out that they can now see who has looked at a post on the forum. Discuss the use cases of this feature.
* Remind them that Taptalk does not update this data.
* Have them browse some of the other division forums and let them know that they can now see all divisions Sgts areas in both the forums and Discord.
* Remind them that they are a guest in those areas and to act appropriately.
* Point out that stealing good ideas from other divisions is encouraged, with credit given as appropriate. Discuss with CO/XO before implementation, however.
* Ask them how they cook their steaks, if they say **'in the Microwave'** then no further action is needed. If they say anything else, tell them how very wrong they are.

**Offer to have the Sgt check with you if they have any further questions.** Arrange a time in about week after training to follow up with the now trained Sgt to check in and see how they are doing in the role.

---

The Sgts Training is now completed - please fill in the appropriate information below and send the newly minted Sgt back to their Division.
MD;
    }

    private function createDivisionChecklistSections(TrainingModule $module): void
    {
        $sections = [
            [
                'title' => 'DNS & Forums',
                'icon' => 'fa-globe',
                'display_order' => 1,
                'content' => 'Set up DNS entry and forum infrastructure for the new division.',
                'checkpoints' => [
                    ['label' => 'DNS Entry', 'description' => $this->getDnsTaskContent()],
                    ['label' => 'Forum Officers Group', 'description' => $this->getForumOfficersGroupContent()],
                    ['label' => 'Custom Style', 'description' => $this->getCustomStyleContent()],
                    ['label' => 'Forum Category', 'description' => $this->getForumCategoryContent()],
                    ['label' => 'Division Forums', 'description' => $this->getDivisionForumsContent()],
                    ['label' => 'Forum Permissions', 'description' => $this->getForumPermissionsContent()],
                ],
            ],
            [
                'title' => 'Forum Configuration',
                'icon' => 'fa-cog',
                'display_order' => 2,
                'content' => 'Configure vbCerberus, application forms, and navigation links.',
                'checkpoints' => [
                    ['label' => 'vbCerberus Entry', 'description' => $this->getVbCerberusContent()],
                    ['label' => 'Application Form', 'description' => $this->getApplicationFormContent()],
                    ['label' => 'Navigation Links', 'description' => $this->getNavigationLinksContent()],
                    ['label' => 'User Profile Fields', 'description' => $this->getUserProfileFieldsContent()],
                ],
            ],
            [
                'title' => 'Discord',
                'icon' => 'fa-discord fab',
                'display_order' => 3,
                'content' => 'Add the division to Discord using the forum bot.',
                'checkpoints' => [
                    ['label' => 'Add Division to Discord', 'description' => $this->getDiscordAddContent()],
                ],
            ],
            [
                'title' => 'Tracker',
                'icon' => 'fa-server',
                'display_order' => 4,
                'content' => 'Create the division in the Tracker Admin CP.',
                'checkpoints' => [
                    ['label' => 'Create Division', 'description' => $this->getTrackerDivisionContent()],
                    ['label' => 'Configure Handle', 'description' => $this->getTrackerHandleContent()],
                ],
            ],
            [
                'title' => 'Cosmetic Items',
                'icon' => 'fa-paint-brush',
                'display_order' => 5,
                'content' => 'Create visual assets for the division.',
                'checkpoints' => [
                    ['label' => 'Division Icon', 'description' => $this->getDivisionIconContent()],
                    ['label' => 'Website Page', 'description' => $this->getWebsitePageContent()],
                    ['label' => 'Division Header', 'description' => $this->getDivisionHeaderContent()],
                    ['label' => 'Tracker Banner', 'description' => $this->getTrackerBannerContent()],
                ],
            ],
        ];

        foreach ($sections as $sectionData) {
            $checkpoints = $sectionData['checkpoints'];
            unset($sectionData['checkpoints']);

            $section = $module->sections()->create($sectionData);

            foreach ($checkpoints as $order => $checkpoint) {
                $section->checkpoints()->create([
                    'label' => $checkpoint['label'],
                    'description' => $checkpoint['description'] ?? null,
                    'display_order' => $order + 1,
                ]);
            }
        }
    }

    private function getDnsTaskContent(): string
    {
        return <<<'MD'
Contact **Archangel**, **Guybrush**, or **Kestah** to create a DNS entry in CloudFlare.

The subdomain should match the division abbreviation (e.g., `bf.clanaod.net` for Battlefield).
MD;
    }

    private function getForumOfficersGroupContent(): string
    {
        return <<<'MD'
1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open `User Groups` → `Add New Usergroup`
3. In `Default Forum Permissions`, select `BasePermissionGroup`
4. Set `Title` to `{Division Name} Officers`
5. Click `Save` without modifying group permissions
MD;
    }

    private function getCustomStyleContent(): string
    {
        return <<<'MD'
1. Open `Styles and Templates` → `Style Manager`
2. Find `ClanAOD.net v4 (Transparent)` → `Add Child Style`
3. Set `Title` to `ClanAOD.net v4 ({Division Name})`
4. Sort styles alphabetically, then `Save Display Order`
5. Edit Style Variables → search `doc_background`
6. Set Background Image to `url(images/warrior/backgrounds/{division bg}.png)`
7. Set Background Image Vertical Offset to `fixed center 150px`
MD;
    }

    private function getForumCategoryContent(): string
    {
        return <<<'MD'
1. Open `Forums & Moderators` → `Add New Forum`
2. Set `Title` to `{Division Name}`
3. Set `Parent Forum` to `No one`
4. Set `Custom Style for this Forum` to the division's style
5. Set `Override Users' Style Choice` to `Yes`
6. Set `Act as Forum` to `No`
7. Sort alphabetically among divisions (use multiples of 10)
MD;
    }

    private function getDivisionForumsContent(): string
    {
        return <<<'MD'
Create forums as needed (typically: recruiting, general chat, members only, officers).

1. `Add New Forum` with title `{Abbreviation} - {Forum Name}`
2. Set `Parent Forum` to `{Division Name}`
3. Apply the division's custom style
4. Sort child forums starting from 1
MD;
    }

    private function getForumPermissionsContent(): string
    {
        return <<<'MD'
Use Permission Duplication Tools with these templates:
- `Permission Setup (Anyone)` - Public forum
- `Permission Setup (Anyone Reply)` - News forum
- `Permission Setup (AOD Only)` - Members only
- `Permission Setup (AOD Sgts+)` - Sgt+ forum

Set `Overwrite Duplicate Entries` and `Overwrite Inherited Entries` to `Yes`.

For officer forums, start with Sgt+ permissions, then add `{Division Name} Officers` group with moderator permissions.
MD;
    }

    private function getVbCerberusContent(): string
    {
        return <<<'MD'
1. Open `Settings` → `Options` → `vb Cerberus`
2. Add entry alphabetically:

```
[subdomain.clanaod.net]
bbtitle=ClanAOD {Division Name} Division
bburl=subdomain.clanaod.net/forums
homeurl=www.clanaod.net
catids=97,{Division category forum id}
styleid={Division style id}
cookiedomain=.clanaod.net
hometitle=ClanAOD.net [{Division Name}]
description=AOD {Division Name} Division Forums!
```
MD;
    }

    private function getApplicationFormContent(): string
    {
        return <<<'MD'
1. Open [Forms Manager](https://www.clanaod.net/forums/forms.php?do=forms)
2. Find `Default Application` → `Copy Form` (1 copy)
3. Edit the copy:
   - Title: `[COLOR=Red]{Division Name} Application[/COLOR]`
   - Clear Category
   - Enable `Post New Thread` → select recruiting forum
MD;
    }

    private function getNavigationLinksContent(): string
    {
        return <<<'MD'
**Application Link:**
1. `Navigation Manager` → `Forum` → find `Apply Here` → `Add Link`
2. Title: `{Division Name}`
3. URL: `//www.clanaod.net/forums/misc.php?do=form&fid={form id}`

**Forum Jump:**
1. Find `Forum Jump` → `Add Link`
2. URL: `//{subdomain}.clanaod.net/forums/forumdisplay.php?f={forum id}`

**CMPS Navigation:**
1. `vBa CMPS` → `Edit Modules` → `[Site Navigation]`
2. Add to `Additional Pages`: Level 2, Link: `//{subdomain}.clanaod.net/forums/`
MD;
    }

    private function getUserProfileFieldsContent(): string
    {
        return <<<'MD'
1. `User Profile Fields` → `User Profile Field Manager`
2. Edit `AOD Gaming Division`
3. Add `{Division Name}` alphabetically to Options

⚠️ **WARNING**: Do not remove divisions until all members are removed.
MD;
    }

    private function getDiscordAddContent(): string
    {
        return <<<'MD'
**Prerequisites:**
- Division must exist in Tracker first
- Forum Officers group must be created

Use `/division add` - autocomplete shows divisions in Tracker not yet in Discord.

The bot automatically maps `{Division Name} Officers` forum group to the `{Division Name} Officer` Discord role.

For help: `/help command:division`
MD;
    }

    private function getTrackerDivisionContent(): string
    {
        return <<<'MD'
Create division in Admin CP with:
- **Name**: Full proper name
- **Slug**: Lowercase, hyphenated (e.g., `world-of-warcraft`)
- **Abbreviation**: **Always lowercase**
- **Officer Role ID**: From Forum Admin → Usergroups
- **Forum App ID**: Division application form ID
- **Description**: Appropriate sub-title
MD;
    }

    private function getTrackerHandleContent(): string
    {
        return <<<'MD'
Select existing handle or create new one:
- **Label**: Proper display name
- **Type**: snake_case (e.g., `bungie_id`)
- **URL**: Deeplink URL with profile ID appended

Check existing handles first - there's a good chance one already exists.
MD;
    }

    private function getDivisionIconContent(): string
    {
        return <<<'MD'
Extract from official game binary using [BeCyIconGrabber](https://jarlpenguin.github.io/BeCyIconGrabberPortable/).

- Size: 48x48 PNG
- Location: `public/images/game_icons/48x48/{abbreviation}.png`
MD;
    }

    private function getWebsitePageContent(): string
    {
        return <<<'MD'
1. Go to [division content](https://github.com/ClanAODDev/aod_site_v2/tree/main/resources/views/division/content)
2. Copy an existing division file as template
3. Update content using basic HTML (`p, ul, strong, em`)
4. H2 headers auto-generate navigation buttons

Contact Arch to deploy changes.
MD;
    }

    private function getDivisionHeaderContent(): string
    {
        return <<<'MD'
1. Use the [Photoshop template](https://github.com/ClanAODDev/aod_site_v2/blob/main/public/images/division-headers/division-header-template.psd)
2. Add official game art (no logos/text) as bottom layer
3. Export as `.jpg` using division abbreviation (lowercase)
4. Add to `aod_site_v2/public/images/division-headers/`
MD;
    }

    private function getTrackerBannerContent(): string
    {
        return <<<'MD'
1. Take the website header image
2. Create new transparent canvas
3. Add image and reduce opacity to 8-10%
4. Export as transparent PNG
5. Add to `tracker_v3/public/images/headers/{abbreviation}.png`
MD;
    }
}
