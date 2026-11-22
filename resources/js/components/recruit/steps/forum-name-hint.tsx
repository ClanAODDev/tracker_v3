import type { RecruitForm } from '@/components/recruit/use-recruit-form';

export function ForumNameHint({ form }: { form: RecruitForm }) {
    const { member, forumNameValidation: v, validating, formattedName } = form;
    if (!member.forum_name) return null;
    if (validating) return <p className="text-xs text-muted-foreground">Checking…</p>;
    if (v.rejectedPrefix === 'aod') return <p className="text-xs text-destructive">Do not include the "AOD_" prefix</p>;
    if (v.rejectedPrefix === 'rank') return <p className="text-xs text-destructive">Name cannot begin with a rank abbreviation</p>;
    if (v.invalidChars) return <p className="text-xs text-destructive">Name cannot contain special characters</p>;
    if (!v.available) return <p className="text-xs text-destructive">This name is already taken</p>;

    const accountName = form.selectedPending
        ? form.emailCheck.found
            ? form.emailCheck.username
            : null
        : form.memberIdValidation.verifiedEmail
          ? form.memberIdValidation.currentUsername
          : null;
    const renaming = accountName && accountName.replace(/^AOD_/i, '').toLowerCase() !== member.forum_name.trim().toLowerCase();

    if (v.valid && renaming)
        return (
            <p className="text-xs text-warning">
                Renames the forum account <strong>{accountName}</strong> to <strong>{formattedName}</strong>. Make sure this is the right person.
            </p>
        );
    if (v.valid && !v.existingAccount)
        return (
            <p className="text-xs text-muted-foreground">
                Will become: <strong>{formattedName}</strong>
            </p>
        );
    return null;
}
