export default {
    title: 'รีเซ็ตรหัสผ่าน',
    heading: 'รีเซ็ตรหัสผ่านของคุณ',
    subtitle: 'กรุณากรอกรหัสผ่านใหม่ด้านล่าง',
    badge: 'รหัสผ่าน',
    fields: {
        email: 'อีเมล',
        password: 'รหัสผ่าน',
        confirmPassword: 'ยืนยันรหัสผ่าน',
    },
    placeholders: {
        email: 'email@example.com',
        password: 'สร้างรหัสผ่านที่ปลอดภัย',
        confirmPassword: 'ยืนยันรหัสผ่านของคุณ',
    },
    passwordRequirements: {
        length: 'อย่างน้อย 8 ตัวอักษร',
        uppercase: 'ตัวอักษรพิมพ์ใหญ่',
        lowercase: 'ตัวอักษรพิมพ์เล็ก',
        number: 'ตัวเลข',
        symbol: 'สัญลักษณ์',
    },
    messages: {
        resetSuccess: 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว',
        invalidInput: 'ข้อมูลไม่ถูกต้อง กรุณาตรวจสอบข้อมูลในแบบฟอร์ม',
        error: 'เกิดข้อผิดพลาด กรุณาตรวจสอบข้อมูลในแบบฟอร์ม',
    },
};
