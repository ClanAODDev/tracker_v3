# Managing Rank
Rank actions are both the rank history of a member and the request process for demotions and promotions. Abilities 
are based on the role of the user, the rank you are wanting to assign to a particular member, and the rank of the 
member being promoted or demoted.

### Promotion Process
- NCO creates rank action*
- Platoon or division leader approves
- Member affected receives discord notification of promotion (demotions do not notify)
- Member accepts or declines promotion, link is good for a limited amount of time
- Upon acceptance, rank is updated on forums and tracker
- If link expires, approver can requeue an acceptance notification

### Additional considerations
* Demotions are automatically approved and accepted
* Denied rank actions are deleted
* Declined rank actions are kept but hidden from member profile

### Rank Thresholds
Automatic approvals are rank changes that are immediately approved and moved to the acceptance process. 
Recommendations must be approved by a platoon or division leader, or an admin.

| Role                    | Can Demote | Automatically Approve Up To | Max Rank to Recommend |      Approve up to      |
|-------------------------|:----------:|:---------------------------:|:---------------------:|:-----------------------:|
| Squad Leader            |     ❌      |             --              |         Spec          |           --            |
| Platoon Leader          |     ❌      |   Pfc <br/>(Configurable)   |          Cpl          | Pfc <br/>(Configurable) |
| Division Leader (CO/XO) |     ✅      |             Cpl             |         SSgt          |           CPL           |
| Admin                   |     ✅      |             Sgt             |        SgtMaj         |         SgtMaj          |