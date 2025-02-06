# Managing Rank
Rank actions are both the rank history of a member and the request process for demotions and promotions. Abilities 
are based on the role of the user, the rank you are wanting to assign to a particular member, and the rank of the 
member being promoted or demoted.

### Promotion Process
- NCO creates a rank action
- Platoon or division leader approves
- Member affected receives discord notification of promotion (demotions do not notify)
- Member accepts or declines promotion, link is good for a limited amount of time
  - If link expires, approver can requeue an acceptance notification- 
- Upon acceptance, rank is updated on forums and tracker

### Additional considerations
* Demotions are automatically approved and accepted - only admin/CO/XO may request
* Denied and declined rank actions are kept for posterity but are hidden from member profile
* General members cannot view rank history

### Rank Thresholds
Automatic approvals are rank changes that are immediately approved and moved to the acceptance process. 
Recommendations must be approved by a platoon or division leader, or an admin.

| Role                    | Can demote |   Auto-approve up to    | Max rank to recommend |      Approve up to      |
|-------------------------|:----------:|:-----------------------:|:---------------------:|:-----------------------:|
| Squad Leader*           |     ❌      |           --            |         Spec          |           --            |
| Platoon Leader*         |     ❌      | Pfc <br/>(Configurable) |          Cpl          | Pfc <br/>(Configurable) |
| Division Leader (CO/XO) |     ✅      |           Cpl           |         SSgt          |           Cpl           |
| Admin                   |     ✅      |           Cpl           |        SgtMaj         |         SgtMaj          |

**Can only promote members within their assigned platoon/squad*

### Access to comments
Comments can be made on rank action recommendations. Access to view comments is based on your rank and the rank 
being considered for the promotion.

- Promotions to **Sgt or Higher:**  
  - Only users with a rank of Master Sergeant or above may comment.

- Promotions to **Cdt, Pvt, or Pfc** - dependent on division configuration
  - Platoon leaders can comment if the rank action falls within their authorized range.

- **All Other Cases:**  
  - Only Division Leaders or Admins can comment.
