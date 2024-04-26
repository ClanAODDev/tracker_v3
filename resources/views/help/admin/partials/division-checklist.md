# Division Checklist

Documentation of the various objects needed for Divisions. This page will generally document the steps and order for
adding a new division. Deviations for deleting a Division will also be noted.

- [DNS Entry](#content-dns-entry)
- [Forums](#content-forums-vbulletin)
    - [Forum Officers Group](#content-forum-officers-group)
    - [Forum Category](#content-forum-category-parent-forum)
    - [Forum Permissions](#content-forum-permission-duplication)
    - [vbCerberus](#content-vbcerberus)
    - [Application Form](#content-application-form)
    - [Navigation](#content-navigation-options)
    - [User Profile Fields](#content-user-profile-fields)
- [Discord](#content-discord)
- [TeamSpeak](#content-teamspeak)
    - [Officer Server Group](#content-officer-server-group)
    - [Flair Server Group](#content-flair-server-groups)
- [Tracker](#content-tracker)

## Tracker

[Creeating divisions on the Tracker](https://www.clanaod.net/forums/showthread.php?t=261830) - Soon to be converted to
Markdown

## DNS Entry

DNS Entries are managed through CloudFlare by Archangel, Guybrush, Kestah, and LiquidSmoke. Please contact one of these
members to add or remove domains using LiquidSmoke as a last resort.

## Forums (vBulletin)

### Forum Officers Group

To create a new forum officers group:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `User Groups` section in the left menu
3. Click `Add New Usergroup`
4. In the `Default Forum Permissions` section, select `BasePermissionGroup`
5. In the `Add New Usergroup` section, set `Title` to `{Division Name} Officers`
6. Scroll to the bottom without modifying the group permissions and click `Save`

### Custom Style

Custom Styles are used to give divisions a unique backgroun image for their forums.

To create a custom style:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `Styles and Templates` section in the left menu
3. Click `Style Manager`
4. In the list of styles, find `ClanAOD.net v4 (Transparent)` and in the options drop down click `Add Child Style`
5. Set `Title` to `ClanAOD.net v4 ({Division Name})`
6. Click `Save`
7. Sort the styles alphabetically using `Display Order` the click `Save Display Order`
8. In the list of styles, find `ClanAOD.net v4 ({Division Name})` and in the options drop down
   click `Edit Style Variables`
9. In `Search Stylevar` enter `doc_background`
10. Set `Backgroun Image` to `url(images/warrior/backgrounds/{division bg}.png)`
11. Set `Background Image Vertical Offset` to `fixed center 150px`
12. Click `Save`

### Forum Category (Parent Forum)

To create a forum category:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `Forums & Moderators` section in the left menu
3. Click `Add New Forum`
4. In the `Add New Forum` section, set `Title` to `{Division Name}`
5. Ensure `Parent Forum` is set to `No one`
6. In the `Style Options` section, set `Custom Style for this Forum` to the division's style
7. Set `Override Users' Style Choice` to `Yes`
8. In the `Posting Options` section, set `Act as Forum` to `No`
9. Scroll to the bottom and click `Save`
10. Click `Forum Manager` from the left menu
11. Sort the division category alphabetically among the divisions using `Display Order`. Note: By convention, multiples
    of 10 are used to make adding new divisions easier.
12. Scroll to the bottom and click `Save Display Order`

### Division Forums

Individual forums are created per division needs. However, typically a recrutiing forum, general chat, members only, and
officers forum are provided.

To create a forum:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `Forums & Moderators` section in the left menu
3. Click `Add New Forum`
4. In the `Add New Forum` section, set `Title` to `{Division Abbreviation} - {Forum Name}`
5. Ensure `Parent Forum` is set to `{Division Name}`
6. In the `Style Options` section, set `Custom Style for this Forum` to the division's style
7. Set `Override Users' Style Choice` to `Yes`
8. Scroll to the bottom and click `Save`
9. Click `Forum Manager` from the left menu
10. Scroll to the division forums and using `Display Order`. Child forums should be sorted starting from 1.
11. Scroll to the bottom and click `Save Display Order`

### Forum Permission Duplication

While vBulletin supports hierachical permissions, it can be complicated to get correct. Because of this, we use template
forums to copy permissions from.

- `Permission Setup (Anyone)`: Public forum
- `Permission Setup (Anyone Reply)`: News forum (only forum moderators can create threads)
- `Permission Setup (AOD Only)`: Members only forum
- `Permission Setup (AOD Sgts+)`: Sgt+ forum
- `Permission Setup (AOD SSgt+)`: SSgt+ forum

To apply permissions to a forum:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `Forums & Moderators` section in the left menu
3. Click `Forum Permissions`
4. In the `Additional Functions` section, click `Permission Duplication Tools`
5. In the `Forum-Based Permission Duplicator` section, select the appropriate Permission Setup forum
   for `Copy Permissions from Forum`
6. Select the appropriate forum(s) to copy permissions to.
7. Set `Overwrite Duplicate Entries` to `Yes`
8. Set `Overwrite Inherited Entries` to `Yes`
9. Click `Go`

#### Officer Forum Permissions

Officer and other specialized forums do not have simple templates to copy from. To apply permissions, start with the
next higher permissions template and adjust.

Officer Forums must start with `Permission Setup (AOD Sgts+)`.

To add officer group permissions after duplicating Sgt+ permissions:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `Forums & Moderators` section in the left menu
3. Click `Forum Permissions`
4. Search for the officer forum by name
5. Underneath the forum name, find the `{Division Name} Officers` group and click `[Edit]`
6. Set permissions according to the forum moderator template.
7. Click `Save`

![Forum Moderator Permissions Template](/images/docs/forum-moderator-permissions.png)

### vbCerberus

vbCerberus allows us to create custom forums views for each division using subdomains.

To create a vbCerberus entry:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `Settings` section in the left menu
3. Click `Options`
4. Select `vb Cerberus` in the `Settings to Edit` list, then click `Edit Settings`
5. Add an entry for the division, alphabetically in to the text box:

```
[subdomain.clanaod.net]
bbtitle=ClanAOD {Division Name} Division
bburl=subdomain.clanaod.net/forums
homeurl=www.clanaod.net
catids=97,{Division category forum id}
styleid={Division style id}
cookiedomain=.clanaod.net
hometitle=ClanAOD.net [{Division Name}]
keywords=
description=AOD {Division Name} Division Forums!
```

6. Click `Save`

### Application Form

To create a division application form:

1. Open the [Forms Manager](https://www.clanaod.net/forums/forms.php?do=forms)
2. Find `Default Application` and click `Copy Form` underneath
3. Enter 1 for the number of times to copy form
4. Find `Default Application - Copy` and click `Edit Form` underneath
5. Click `Form Title and Description` in the tabs
5. Set `Title` to `[COLOR=Red]{Division Name} Application[/COLOR]`
6. Clear `Category`
7. Click `Save Changes`
8. Click `Form Actions` in the tabs
9. Enable `Post New Thread`
10. Select the recruiting forum for the division
11. Click `Save Changes`

### Navigation Options

#### Application

To create the Division Application link:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `Settings` section in the left menu
3. Click on `Navigation Manager`
4. Click on `Forum`
5. Find `Apply Here` and select `Add Link` in the edit dropdown
6. Set Title to `{Division Name}`
7. Set Target URL to the division application form excluding
   https: `//www.clanaod.net/forums/misc.php?do=form&fid={Division application form id})`
8. Set `Active` to `Yes`
9. Click `Save`
10. Sort the links alphabetically using `Display Order`
11. Scroll to the bottom and click `Save

#### Forum Jump

To create the Forum Jump navigation option:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `Settings` section in the left menu
3. Click on `Navigation Manager`
4. Click on `Forum`
5. Find `Forum Jump` and select `Add Link` in the edit dropdown
6. Set Title to `{Division Name}`
7. Set Target URL to the division category forum using the subdomain and excluding
   https: `//{subdomain}.clanaod.net/forums/forumdisplay.php?f={Division category forum id})`
8. Set `Active` to `Yes`
9. Click `Save`
10. Sort the links alphabetically using `Display Order`
11. Scroll to the bottom and click `Save`

#### CMPS Navigation Entry

CMPS is the plugin that manages the front page of the forums. We must add a link to each division to the left menu.

To create the CMPS navigation option:

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `vBa CMPS` section in the left menu
3. Click the `Edit Modules`
4. Click on `[Site Navigation]`
5. Scroll to `Site Navigation Options` section at the bottom of the page
6. Edit the first blank line in `Additional Pages` for the new link
7. Set `Level` to 2
8. Set `Link` to the subdomain forum url excluding https: `//{subdomain}.clanaod.net/forums/`
9. Set `Text` to `{Division Name}`
10. Sort the links alphabetically using `Order`
11. Click `Save`

### User Profile Fields

1. Open the [AdminCP](https://www.clanaod.net/forums/admincp/index.php)
2. Open the `User Profile Fields` section in the left menu
3. Click `User Profile Field Manager`
4. Click `Edit` beside `AOD Gaming Division`
5. In `Options` add `{Division Name}` to the list in alphabetical order
6. Click `Save`

__WARNING__: Do not remove divisions from the list until all members have been removed from the division. Check
the [Forum Member List](https://www.clanaod.net/forums/showroster.php) to confirm.

## Discord

Discord is managed entirely through the `ClanAOD.net Forum Bot` using slash commands. For help with the bot,
use `/help command:division` to see all available division commands.

### Adding a Division

To add a division in Discord, ensure the Division has first been created in Tracker and that the appropriate Officers
group has been created on the forums.

Auto complete for `/division add` will show divisions that exist in the tracker that have not yet been added to discord.
While you can type a custom name, it is highly recommended to use auto complete.

#### Forum Sync Map

The forum sync process maps Discord roles to forum groups. When creating a division, the bot will automatically
choose `{Division Name} Officers` to map to the `{Division Name} Officer` role. If the forum group does not exist or the
bot cannot find it, an error will be reported during division creation.

TODO: Add steps for manual mapping

### Deleting a Division

Auto complete for `/division delete` will show divisions that exist in the tracker that have been added to discord.
While you can type a custom name, it is highly recommended to use auto complete.

## TeamSpeak

### Officer Server Group

Officer Server Groups provides an easy way to identify division officers on TeamSpeak and gives officers permissions
required to manage new recruits.

__WARNING__: TeamSpeak Server Groups must be edited using the advanced permissions editor only.

To create a new group:

1. Connect to the AOD TeamSpeak Server
2. Select `Permissions` &#8594; `Server Groups`
3. Right click on an existing officer group and select `Copy`
4. Edit the `Target Name` to `{Division Name} Officer` and click `Ok`
5. Select the group just created
6. Enable the `Show Granted Only` option in Permission view
7. Set `i_icon_id` to the appropriate icon for the division (or clear it until the icon is uploaded)

![TeamSpeak Officer Server Group permissions](/images/docs/officer-server-group.png)

#### Forum Sync Map

The forum sync process maps TeamSpeak server groups to forum groups. Presently this processes is managed directly from
AOD's server. Please contact Archangel to add or remove group maps.

### Flair Server Groups

Flair Server Groups allow divisions to identify important groups within the division, such as the Reapers competetive
teams.

__WARNING__: TeamSpeak Server Groups must be edited using the advanced permissions editor only.

To create a new group:

1. Connect to the AOD TeamSpeak Server
2. Select `Permissions` &#8594; `Server Groups`
3. Right click on an existing flair group and select `Copy`
4. Edit the `Target Name` to `{Division Name} [Group Name]` and click `Ok`
5. Select the group just created
6. Enable the `Show Granted Only` option in Permission view
7. Set `i_icon_id` to the appropriate icon for the flair group (or clear it until the icon is uploaded)

![TeamSpeak Flair Server Group permissions](/images/docs/flair-server-group.png)

## Tracker

Rough notes here. Needs some cleaning up.

- Forum officer role (Division Officers)
- Agreed upon abbreviation (short-hand version of a division name. IE., Battlefield => bf, World of Warcraft => wow
- Forum Form Application (IE., [url]https://www.clanaod.net/forums/forms.php?do=form&fid=34[/url])
- Ingame handle (developer or platform specific). There's a good chance it already exists. If not, make a new one (
  tracker admin cp => Handles)
- Division icon - this should **come from the official game binary** and can be extracted
  using [BeCyIconGrabber]("https://jarlpenguin.github.io/BeCyIconGrabberPortable/") (48x48 PNG). If you are unsure how
  to use this, ask around. Store the division icon
  at [[FONT=Courier New]public/images/game_icons/48x48[/FONT]]("https://github.com/ClanAODDev/tracker_v3/tree/main/public/images/game_icons/48x48")
  using the division abbreviation.[/INDENT]

### Admin CP > Divisions > Create Division

- Name: Provide the full proper name of the division
- Slug: Sluggified version of the name. This should be lower-case and hyphenated. No other characters.
- Abbreviation: should match the forum. **Always lowercase. This is important.**
- Officer role id: Id of the role/group created in the forums. You can find this under Forum Admin > Usergroups > Id
  listed on the right-hand side in the "Edit" dropdown
- Handle: Should already exist. Pick the most appropriate one. More information provided below regarding handles.
- Forum app ID: The form id of the division application
- Settings: Leave this blank. It'll be populated later.
- Created At, Updated At: Currently defaults to blank. Set this to today's date.
- Description: Provide some appropriate sub-title for the division.
- Active: Self-explanatory

### Handles

Handles are ingame names that a member can have across the variety of platforms of games AOD plays. Steam, Battlenet,
and Bungie are just a few examples. Some games (like Warships) have their own specific ingame handle. Since handles are
used in a variety of ways, it's important to make sure the handle is configured correctly.

- Label: Mostly cosmetic. Give it a proper name.
- Type: This is less obvious (and probably needs some UI improvement), but it's the snake_case version of the name. IE.,
  Bungie => bungie_id.
- Comments: mostly guidance, not required
- URL: This is the deeplink URL, if the platform provides it. Ensure that the URL is configured so that the unique
  profile/member id can be appended to the end. Not all platforms offer this.[/INDENT]

### Additional cosmetic items

#### Divisional website pages

Since divisions automatically appear on the website when they are populated in the tracker, a new problem arises: the
link to the division leads to a 404. To remedy this, a division page must be created as soon as possible to ensure that
traffic doesn't get lost, and divisions actually have a way to recruit people (or at least send them to the correct
form).

The website repository is publicly accessible and anyone can contribute changes - even directly through the UI. But
you'll need a GitHub account to do so.

- Go to the AOD Repo and look at division page
  content: https://github.com/ClanAODDev/aod_site_v2/tree/main/resources/views/division/content
- Copy content from an existing division and make a new file. Look at multiple divisions so you can get a sense for
  what's possible and how to do it correctly. Stick with basic html formatting elements (`p, ul, strong, underline, em,
  etc`)
- Update content as necessary. Preserve the html structure (classnames, element ordering).
- Ask Arch or myself to update the website with changes from the repo. At some point we'll automate this so that PRs
  automatically get deployed. For now, it's manual.

Templates are structured such that H2 header elements will automatically generate a button at the top of the page,
providing a table of contents to navigate. The ID can be anything, as long as it's unique, and uses
the-correct-character-structure.

#### Division page headers

Header images are based on
a [preexisting photoshop template](https://github.com/ClanAODDev/aod_site_v2/blob/main/public/images/division-headers/division-header-template.psd).
Notice that the actual image should be the bottom layer of the image. _It should also not include any
logos or text, and should generally be official game art, not fan-made._ It should be exported as
a `.jpg` and added to the [aod_site_v2/public/images/division-headers](https://github.com/ClanAODDev/aod_site_v2/tree/main/public/images/division-headers)
folder using the **division abbreviation (LOWERCASE)**.

Only add game artwork underneath the "division content" folder layer. Do not make any other changes to the template, or
it will break other elements of the site.

Assuming you provided a forum_app_id on the tracker, an "apply" button will send them directly to your division
application. This is automatically added to the page. **If the apply button takes you to a 404, it's because you're
missing the forum_app_id for the division on the tracker.**

#### Divisional tracker banner

This is the image that is displayed at the top of the tracker for members of the division, if one exists. The way this
image gets created is by taking the same image used on the website into a new photoshop canvas (must be transparent),
adding the image, and reducing the opacity to 8-10%. You can take an existing tracker banner and remove the content to
make things easier.

You can see existing headers here: https://github.com/ClanAODDev/tracker_v3/tree/main/public/images/headers

Note the naming convention - use the abbreviation and ensure you export as a transparent PNG.

If there any questions about this process, please don't hesitate to ask.
