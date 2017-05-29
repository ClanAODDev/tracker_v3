<template>
    <!--TODO - Move to Admin CP, store descriptions in positions -->
    <div>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Role</th>
                <th>Access</th>
            </tr>
            </thead>
            <tbody>
            <tr :class="role == 1 ? 'active c-accent' : ''">
                <td>Member</td>
                <td>
                    <ul>
                        <li>View access only</li>
                    </ul>
                </td>
            </tr>
            <tr :class="role == 2 ? 'active c-accent' : ''">
                <td>Officer (new NCO)</td>
                <td>
                    <ul>
                        <li>Can manage members within own platoon</li>
                    </ul>
                </td>
            </tr>
            <tr :class="role == 3 ? 'active c-accent' : ''">
                <td>Jr Leader (CPL)</td>
                <td>
                    <ul>
                        <li>Can manage own division platoon, squads, members</li>
                    </ul>
                </td>
            </tr>
            <tr :class="role == 4 ? 'active c-accent' : ''">
                <td>Sr Leader (SGT+)</td>
                <td>
                    <ul>
                        <li>Can manage (and create, delete) any division platoons, squads, members</li>
                        <li>Can remove any member</li>
                        <li>Can change own division settings</li>
                        <li>Can give user account access (up to one below current role)</li>
                    </ul>
                </td>
            </tr>
            <tr :class="role == 5 ? 'active c-accent' : ''">
                <td>Admin (MSGT+)</td>
                <td>
                    <ul>
                        <li>Inherits Sr Leader Permissions</li>
                        <li>Can change any division settings</li>
                        <li>Admin CP</li>
                    </ul>
                </td>
            </tr>
            </tbody>

        </table>

    </div>
</template>

<script>
    export default {
        props: ['role']
    }
</script>