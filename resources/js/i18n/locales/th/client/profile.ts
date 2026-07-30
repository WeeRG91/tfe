export default {
    pageTitle: 'โปรไฟล์ของฉัน',
    eyebrow: 'บัญชีของฉัน',
    title: 'โปรไฟล์ของฉัน',
    description: 'จัดการการตั้งค่าและการกำหนดลักษณะบัญชีของคุณ',
    tabs: {
        info: {
            label: 'ข้อมูลโปรไฟล์',
            description: 'แก้ไขข้อมูลส่วนตัวของคุณ',
        },
        password: {
            label: 'รหัสผ่าน',
            description: 'เปลี่ยนรหัสผ่านของคุณ',
        },
        twoFactor: {
            label: 'การยืนยันตัวตนสองขั้นตอน (2FA)',
            description: 'เพิ่มความปลอดภัยให้กับบัญชีของคุณ',
        },
    },
    infoTab: {
        title: 'ข้อมูลโปรไฟล์',
        editProfile: 'แก้ไขโปรไฟล์',
        verification: {
            unverified: 'อีเมลของคุณยังไม่ได้รับการยืนยัน',
            resend: 'คลิกที่นี่เพื่อส่งอีเมลยืนยันอีกครั้ง',
            sent: 'ลิงก์ยืนยันใหม่ถูกส่งไปยังอีเมลของคุณแล้ว',
            sentSuccess: 'ส่งอีเมลยืนยันเรียบร้อยแล้ว',
            sentFailed: 'ไม่สามารถส่งอีเมลยืนยันได้ โปรดลองอีกครั้ง',
        },
        photo: {
            label: 'รูปโปรไฟล์',
            change: 'เปลี่ยนรูป',
            requirements: 'JPG, JPEG, PNG, GIF, WEBP • สูงสุด 2 MB',
            tooLarge: 'ขนาดรูปภาพต้องไม่เกิน 2 MB',
            invalidType:
                'โปรดอัปโหลดรูปภาพที่ถูกต้อง (JPEG, PNG, GIF หรือ WEBP)',
            alt: 'รูปโปรไฟล์',
        },
        form: {
            fullName: 'ชื่อ-นามสกุล',
            fullNamePlaceholder: 'กรอกชื่อ-นามสกุล',
            email: 'อีเมล',
            emailPlaceholder: 'กรอกอีเมล',
            save: 'บันทึก',
            saving: 'กำลังบันทึก...',
            cancel: 'ยกเลิก',
        },
        delete: {
            title: 'ลบบัญชี',
            description:
                'เมื่อคุณลบบัญชี ข้อมูลและทรัพยากรทั้งหมดจะถูกลบอย่างถาวร โปรดตรวจสอบให้แน่ใจก่อนดำเนินการต่อ',
            button: 'ลบบัญชี',
        },
        success: {
            updated: 'อัปเดตโปรไฟล์เรียบร้อยแล้ว!',
        },
    },
    passwordTab: {
        title: 'เปลี่ยนรหัสผ่าน',
        description:
            'ใช้รหัสผ่านที่รัดกุมเพื่อเพิ่มความปลอดภัยให้กับบัญชีของคุณ',
        form: {
            currentPassword: 'รหัสผ่านปัจจุบัน',
            currentPasswordPlaceholder: 'กรอกรหัสผ่านปัจจุบัน',
            newPassword: 'รหัสผ่านใหม่',
            newPasswordPlaceholder: 'กรอกรหัสผ่านใหม่',
            confirmPassword: 'ยืนยันรหัสผ่านใหม่',
            confirmPasswordPlaceholder: 'ยืนยันรหัสผ่านใหม่',
            update: 'อัปเดตรหัสผ่าน',
            updating: 'กำลังอัปเดต...',
        },
        passwordStrength: {
            minLength: 'อย่างน้อย 8 ตัวอักษร',
            uppercase: 'ตัวพิมพ์ใหญ่',
            lowercase: 'ตัวพิมพ์เล็ก',
            number: 'ตัวเลข',
            symbol: 'อักขระพิเศษ',
        },
        success: {
            updated: 'อัปเดตรหัสผ่านเรียบร้อยแล้ว!',
        },
    },
    twoFactorTab: {
        title: 'การยืนยันตัวตนสองขั้นตอน',
        description: 'เพิ่มความปลอดภัยอีกขั้นให้กับบัญชีของคุณ',
        status: {
            enabled: 'เปิดใช้งาน',
            disabled: 'ปิดใช้งาน',
            protected: 'บัญชีของคุณได้รับการปกป้องด้วย 2FA',
            unprotected: 'บัญชีของคุณยังไม่ได้เปิดใช้ 2FA',
        },
        actions: {
            enable: 'เปิดใช้งาน 2FA',
            disable: 'ปิดใช้งาน 2FA',
            loading: 'กำลังโหลด...',
            verify: 'ยืนยันและเปิดใช้งาน',
            verifying: 'กำลังยืนยัน...',
            showRecoveryCodes: 'แสดงรหัสกู้คืน',
            hideRecoveryCodes: 'ซ่อนรหัสกู้คืน',
            copy: 'คัดลอก',
            copied: 'คัดลอกแล้ว!',
            download: 'ดาวน์โหลด',
            regenerate: 'สร้างใหม่',
        },
        setup: {
            step1Title: 'ขั้นตอนที่ 1: สแกน QR Code',
            step1Description:
                'สแกน QR Code ด้วยแอปยืนยันตัวตนของคุณ (Google Authenticator, Authy เป็นต้น)',
            manualEntry:
                'หากไม่สามารถสแกน QR Code ได้ ให้ป้อนรหัสลับนี้ลงในแอปยืนยันตัวตนของคุณด้วยตนเอง',
            appName: 'ชื่อแอป',
            account: 'บัญชี',
            step2Title: 'ขั้นตอนที่ 2: ยืนยันรหัส',
            step2Description: 'กรอกรหัส 6 หลักจากแอปยืนยันตัวตน',
            verificationCode: 'รหัสยืนยัน',
            verificationPlaceholder: 'กรอกรหัส 6 หลัก',
            incompleteCode: 'กรุณากรอกรหัสให้ครบทั้ง 6 หลัก',
        },
        recoveryCodes: {
            title: 'บันทึกรหัสกู้คืนของคุณ',
            description:
                'รหัสเหล่านี้ใช้เข้าสู่ระบบได้หากคุณสูญเสียอุปกรณ์ยืนยันตัวตน โปรดเก็บไว้ในที่ปลอดภัย',
        },
        enabledNotice: {
            title: 'เปิดใช้งานการยืนยันตัวตนสองขั้นตอนแล้ว',
            description:
                'บัญชีของคุณได้รับการปกป้องด้วยความปลอดภัยเพิ่มเติม คุณจะต้องใช้แอปยืนยันตัวตนเพื่อเข้าสู่ระบบ',
        },
        success: {
            setupStarted: 'สแกน QR Code ด้วยแอปยืนยันตัวตนของคุณ',
            enabled: 'เปิดใช้งานการยืนยันตัวตนสองขั้นตอนเรียบร้อยแล้ว!',
            disabled: 'ปิดใช้งานการยืนยันตัวตนสองขั้นตอนแล้ว',
            regenerated: 'สร้างรหัสกู้คืนใหม่เรียบร้อยแล้ว!',
            copiedSecretKey: 'คัดลอกรหัสลับแล้ว',
        },
        errors: {
            setupFailed: 'ไม่สามารถตั้งค่าการยืนยันตัวตนสองขั้นตอนได้',
            invalidCode: 'รหัสยืนยันไม่ถูกต้อง โปรดลองอีกครั้ง',
            disableFailed: 'ไม่สามารถปิดใช้งานการยืนยันตัวตนสองขั้นตอนได้',
            regenerateFailed: 'ไม่สามารถสร้างรหัสกู้คืนใหม่ได้',
            loadRecoveryCodes: 'ไม่สามารถโหลดรหัสกู้คืนได้',
            copyFailed: 'ไม่สามารถคัดลอกรหัสกู้คืนได้',
        },
    },
    confirmPasswordModal: {
        password: 'รหัสผ่าน',
        passwordPlaceholder: 'กรอกรหัสผ่านของคุณ',
        cancel: 'ยกเลิก',
        confirm: 'ยืนยัน',
        confirming: 'กำลังตรวจสอบ...',
        close: 'ปิด',
        enableTwoFactor: {
            title: 'เปิดใช้งานการยืนยันตัวตนสองขั้นตอน',
            description: 'กรอกรหัสผ่านเพื่อเปิดใช้งานการยืนยันตัวตนสองขั้นตอน',
            confirm: 'เปิดใช้งาน 2FA',
        },
        disableTwoFactor: {
            title: 'ปิดใช้งานการยืนยันตัวตนสองขั้นตอน',
            description: 'กรอกรหัสผ่านเพื่อปิดใช้งานการยืนยันตัวตนสองขั้นตอน',
            confirm: 'ปิดใช้งาน 2FA',
        },
        regenerateCodes: {
            title: 'สร้างรหัสกู้คืนใหม่',
            description: 'กรอกรหัสผ่านเพื่อสร้างรหัสกู้คืนใหม่',
            confirm: 'สร้างรหัสใหม่',
        },
        default: {
            title: 'ยืนยันรหัสผ่าน',
            description: 'กรอกรหัสผ่านของคุณ',
        },
        errors: {
            required: 'กรุณากรอกรหัสผ่าน',
            invalid: 'รหัสผ่านไม่ถูกต้อง โปรดลองอีกครั้ง',
            verificationFailed: 'ไม่สามารถตรวจสอบรหัสผ่านได้ โปรดลองอีกครั้ง',
        },
    },
};
