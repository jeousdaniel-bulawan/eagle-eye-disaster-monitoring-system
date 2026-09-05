async function writeAuditLog(action, targetUserId, oldData, newData, notes) {
    if (!window.supabase) return;
    try {
        const actorId = localStorage.getItem('userId') || 'SYSTEM';
        const actorRole = (localStorage.getItem('userRole') || 'unknown').toLowerCase();
        const sanitize = (obj) => {
            if (!obj) return null;
            const safe = Object.assign({}, obj);
            if (safe.password) safe.password = '[REDACTED]';
            if (safe.profile_pic && typeof safe.profile_pic === 'string' && safe.profile_pic.length > 100) {
                safe.profile_pic = '[BASE64_IMAGE]';
            }
            return safe;
        };
        await window.supabase.from('user_audit_log').insert([{
            action: action,
            user_id: actorId,
            actor_role: actorRole,
            subject_id: targetUserId || null,
            old_data: sanitize(oldData),
            new_data: sanitize(newData),
            notes: notes || null,
            changed_at: new Date().toISOString()
        }]);
    } catch (err) {
        console.warn('Audit log write failed:', err.message);
    }
}