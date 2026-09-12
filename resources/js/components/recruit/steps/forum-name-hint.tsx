import type { RecruitForm } from '@/components/recruit/use-recruit-form';

export function ForumNameHint({ form }: { form: RecruitForm }) {
    const { member, forumNameValidation: v, validating, formattedName } = form;
    if (!member.forum_name) return null;
    if (validating) return <p className="text-xs text-muted-foreground">Checking…</p>;
    if (v.rejectedPrefix === 'aod') return <p className="text-xs text-destructive">Do not include the "AOD_" prefix</p>;
    if (v.rejectedPrefix === 'rank')
        return <p className="text-xs text-destructive">Name cannot begin with a rank abbreviation</p>;
    if (v.invalidChars) return <p className="text-xs text-destructive">Name cannot contain special characters</p>;
    if (!v.available) return <p className="text-xs text-destructive">This name is already taken</p>;
    if (v.valid && !v.existingAccount)
        return (
            <p className="text-xs text-muted-foreground">
                Will become: <strong>{formattedName}</strong>
            </p>
        );
    return null;
}
