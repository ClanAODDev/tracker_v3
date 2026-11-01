# Managing Rank
Rank actions are both the rank history of a member and the request process for demotions and promotions. Abilities 
are based on the role of the user, the rank you are wanting to assign to a particular member, and the rank of the 
member being promoted or demoted.

### Promotion Process
- NCO or senior leader creates a rank action
- Platoon leader, senior leader or division leader approves, within the limits below
- Promotions up to Pfc are applied as soon as they are approved, with no acceptance step
- For promotions above Pfc, the member receives a Discord notification and accepts or declines the promotion. The link is good for a limited amount of time
  - If the link expires, the approver can requeue an acceptance notification
- Upon approval (up to Pfc) or acceptance (above Pfc), rank is updated on forums and tracker

### Additional considerations
* Demotions are automatically approved and accepted - only admin/CO/XO may request
* Promotions to Sergeant and above can only be approved by a Command Sergeant or higher
* Lance Corporal and Corporal promotions need a CO/XO or an admin
* Denied and declined rank actions are kept for posterity but are hidden from member profile
* General members cannot view rank history

### Rank Thresholds
Automatic approvals are rank changes that are immediately approved. Up to Pfc they are applied right away; above Pfc they move to the acceptance process. 
Recommendations must be approved by a platoon leader, senior leader or division leader, or an admin.

| Role                    | Can demote |   Auto-approve up to    | Max rank to recommend |      Approve up to      |
|-------------------------|:----------:|:-----------------------:|:---------------------:|:-----------------------:|
| Squad Leader*           |     ❌      |           --            |         Spec          |           --            |
| Platoon Leader*         |     ❌      | Pfc <br/>(Configurable) |          Cpl          | Pfc <br/>(Configurable) |
| Senior Leader**         |     ❌      | Pfc <br/>(Configurable) |         SSgt          | Pfc <br/>(Configurable) |
| Division Leader (CO/XO) |     ✅      |           Cpl           |         SSgt          |           Cpl           |
| Admin                   |     ✅      |           Cpl           |        SgtMaj         |         SgtMaj          |

**Unit leaders can only promote members within the unit they lead. A leader of the lowest level of the division has squad leader limits, a leader of any level above it has platoon leader limits.*

***Senior leaders can request a rank action for any member of their own division, whatever unit the member is in (or none), and approve rank actions for members of their division up to the division's platoon leader limit. They cannot approve their own rank action or a rank above their own.*

### Access to comments
Comments can be made on rank action recommendations. Access to view comments is based on your rank and the rank 
being considered for the promotion.

##### Promotions to Sgt or Higher
- Only users with a rank of Master Sergeant or above may comment

##### Promotions to Cdt, Pvt, or Pfc
- Dependent on division configuration
- Platoon leaders may comment if the rank action falls within their authorized range

##### All Other Cases
- Only Division Leaders or Admins can comment
