export default {
    title: 'เปิดใช้งานบัญชี',
    activate: 'เปิดใช้งานบัญชี',
    heading: {
        title: 'ตั้งรหัสผ่านของคุณ',
        subtitle:
            'สร้างรหัสผ่านที่ปลอดภัยเพื่อดำเนินการตั้งค่าบัญชีให้เสร็จสมบูรณ์',
        badge: 'ยินดีต้อนรับ',
    },
    fields: {
        name: 'ชื่อ',
        email: 'อีเมล',
        password: 'รหัสผ่าน',
        passwordPlaceholder: 'สร้างรหัสผ่านที่ปลอดภัย',
        confirmPassword: 'ยืนยันรหัสผ่าน',
        confirmPasswordPlaceholder: 'ยืนยันรหัสผ่านของคุณ',
    },
    messages: {
        success: 'เปิดใช้งานบัญชีเรียบร้อยแล้ว!',
        invalidInput: 'ข้อมูลไม่ถูกต้อง',
        error: 'เกิดข้อผิดพลาด โปรดตรวจสอบแบบฟอร์มอีกครั้ง',
    },
    passwordRequirements: {
        characters: 'อย่างน้อย 8 ตัวอักษร',
        uppercase: 'ตัวพิมพ์ใหญ่',
        lowercase: 'ตัวพิมพ์เล็ก',
        number: 'ตัวเลข',
        symbol: 'สัญลักษณ์',
    },
};
