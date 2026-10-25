import crypto from 'crypto';
import bcrypt from 'bcryptjs';
import db, { getOne, run } from '../db.js';

export function setAdminPassword(newPassword = null) {
    const adminUser = getOne("SELECT id, name, email, role FROM users WHERE role = 'admin' OR id = 1 LIMIT 1");
    if (!adminUser) {
        throw new Error('No admin user found in database');
    }

    let password = newPassword;
    let isGenerated = false;

    if (!password) {
        // Generate secure 16-char random password
        password = crypto.randomBytes(10).toString('base64url').slice(0, 16) + '@VT';
        isGenerated = true;
    }

    const hashedPassword = bcrypt.hashSync(password, 12);

    run("UPDATE users SET password = ?, updated_at = datetime('now') WHERE id = ?", [
        hashedPassword,
        adminUser.id
    ]);

    console.log('========================================================================');
    console.log(' SUPER-ADMIN CREDENTIAL MANAGEMENT');
    console.log('========================================================================');
    console.log(`Admin User ID : ${adminUser.id}`);
    console.log(`Admin Name    : ${adminUser.name}`);
    console.log(`Admin Email   : ${adminUser.email}`);
    console.log(`Role          : ${adminUser.role}`);
    console.log(`Status        : Password updated successfully.`);
    if (isGenerated) {
        console.log(`Temporary Password Generated: [${password}]`);
        console.log('ACTION REQUIRED: Change this password upon first login.');
    } else {
        console.log('Custom password has been hashed with bcrypt (rounds: 12) and saved.');
    }
    console.log('========================================================================');

    return { adminUser, password };
}

if (process.argv[1]?.endsWith('set_admin_password.js')) {
    const customPass = process.argv[2] || null;
    setAdminPassword(customPass);
}
