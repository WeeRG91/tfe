export default {
    title: 'สมัครสมาชิก',
    heading: 'สร้างบัญชี',
    subtitle: 'กรอกข้อมูลด้านล่างเพื่อสร้างบัญชีของคุณ',
    badge: 'ยินดีต้อนรับ',
    fields: {
        name: 'ชื่อ',
        email: 'อีเมล',
        password: 'รหัสผ่าน',
        confirmPassword: 'ยืนยันรหัสผ่าน',
    },
    placeholders: {
        name: 'ชื่อ-นามสกุล',
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
        created: 'สร้างบัญชีเรียบร้อยแล้ว',
        invalidInput: 'ข้อมูลไม่ถูกต้อง กรุณาตรวจสอบข้อมูลในแบบฟอร์ม',
        error: 'เกิดข้อผิดพลาด กรุณาตรวจสอบข้อมูลในแบบฟอร์ม',
    },
};
