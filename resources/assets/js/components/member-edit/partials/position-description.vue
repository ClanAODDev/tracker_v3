<template>
    <!--TODO - Move to Admin CP, store descriptions in positions -->
    <div>
        <div v-show="position == 1">
            <h4>Member</h4>
            <p>Assigned to a platoon or squad, and has no responsibilities within a division other than to play and be active.</p>
        </div>

        <div v-show="position == 2">
            <h4>Squad Leader</h4>
            <p>Junior level officer. Typically the first position a new NCO takes on. Responsibilities consist primarily of recruiting for a division, in addition to whatever responsibilities your division requires of new NCOs.</p>
        </div>

        <div v-show="position == 3">
            <h4>Platoon Leader</h4>
            <p>Mid-grade NCO, typically responsible for a collection of squads and squad leaders. A platoon can have only one platoon leader shown.</p>
        </div>

        <div v-show="position == 4">
            <h4>General Sergeant</h4>
            <p>A Sergeant who is not specifically assigned a leadership role within a division is typically added to "General Sergeants".</p>
            <p>This position populates the "General Sergeant" area of a division's page on the tracker.</p>
        </div>

        <div v-show="position == 5">
            <h4>Executive Officer</h4>
            <p>Second-most senior division NCO. This position, in conjunction with the
                <code>Senior Leader</code> role, allows a member to make changes to division settings.</p>
            <p>This position populates the "Division Leadership" area of a division's page on the tracker.</p>
        </div>

        <div v-show="position == 6">
            <h4>Commanding Officer</h4>
            <p>Senior division NCO. This position, in conjunction with the
                <code>Senior Leader</code> role, allows a member to make changes to division settings.</p>
            <p>This position populates the "Division Leadership" area of a division's page on the tracker.</p>
        </div>

        <div v-show="position == 7">
            <h4>Clan Admin</h4>
            <p>Senior clan leader, typically MSgt or higher. This position typically has no direct responsibilities inside a single division, and instead assists with management of all divisions across the clan.</p>
        </div>

    </div>
</template>

<script>
  export default {
    props: ['position']
  };
</script>