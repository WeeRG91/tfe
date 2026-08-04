export default {
    title: 'ผู้ใช้งาน',
    subtitle: 'จัดการผู้ใช้งานและบทบาทของพวกเขา',
    messages: {
        noUsers: 'ไม่พบผู้ใช้งาน',
        noSearchResults: 'ไม่พบผู้ใช้งานที่ตรงกับ “{query}”',
        joined: 'เข้าร่วมเมื่อ: {date}',
        loadMore: 'โหลดผู้ใช้งานเพิ่มเติม',
        confirmInactivate: 'คุณแน่ใจหรือไม่ว่าต้องการปิดใช้งานผู้ใช้งานนี้?',
        confirmReactivate:
            'คุณแน่ใจหรือไม่ว่าต้องการเปิดใช้งานผู้ใช้งานนี้อีกครั้ง?',
        confirmDelete: 'คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้งานนี้อย่างถาวร?',
    },
    errors: {
        loadFailed: 'ไม่สามารถโหลดผู้ใช้งานได้',
        inactivateFailed: 'ไม่สามารถปิดใช้งานผู้ใช้งานได้',
        reactivateFailed: 'ไม่สามารถเปิดใช้งานผู้ใช้งานอีกครั้งได้',
        deleteFailed: 'ไม่สามารถลบผู้ใช้งานได้',
    },
    details: {
        joined: 'เข้าร่วมเมื่อ {date}',
        stats: {
            roles: 'บทบาท',
            totalPoints: 'คะแนนทั้งหมด',
            orders: 'คำสั่งซื้อ',
            permissions: 'สิทธิ์การใช้งาน',
        },
        permissions: {
            title: 'บทบาทและสิทธิ์การใช้งาน',
            assignedRoles: 'บทบาทที่ได้รับ',
            allPermissions: 'สิทธิ์การใช้งานทั้งหมด',
            uncategorized: 'ไม่มีหมวดหมู่',
            extra: 'เพิ่มเติม',
            both: 'ทั้งสองแบบ',
            noPermissions: 'ยังไม่ได้กำหนดสิทธิ์การใช้งาน',
            noRoles: 'ยังไม่ได้กำหนดบทบาทให้ผู้ใช้งานนี้',
        },
        orders: {
            title: 'ประวัติคำสั่งซื้อ',
            noOrders: 'ไม่พบคำสั่งซื้อของผู้ใช้งานนี้',
        },
        loyaltyPoints: {
            title: 'ประวัติคะแนนสะสม',
            noHistory: 'ไม่มีประวัติคะแนนสะสม',
        },
        dates: { createdAt: 'สร้างเมื่อ:', lastUpdated: 'อัปเดตล่าสุด:' },
        messages: {
            confirmInactivate:
                'คุณแน่ใจหรือไม่ว่าต้องการปิดใช้งานผู้ใช้งานนี้?',
            confirmReactivate:
                'คุณแน่ใจหรือไม่ว่าต้องการเปิดใช้งานผู้ใช้งานนี้อีกครั้ง?',
            confirmDelete: 'คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้งานนี้อย่างถาวร?',
        },
        errors: {
            inactivateFailed: 'ไม่สามารถปิดใช้งานผู้ใช้งานได้',
            reactivateFailed: 'ไม่สามารถเปิดใช้งานผู้ใช้งานอีกครั้งได้',
            deleteFailed: 'ไม่สามารถลบผู้ใช้งานได้',
        },
    },
    form: {
        editTitle: 'แก้ไขผู้ใช้งาน',
        sections: {
            userInformation: 'ข้อมูลผู้ใช้งาน',
            roleAssignment: 'กำหนดบทบาท',
            permissions: 'สิทธิ์การใช้งาน',
        },
        fields: {
            fullName: 'ชื่อ-นามสกุล',
            emailAddress: 'อีเมล',
            password: 'รหัสผ่าน',
            confirmPassword: 'ยืนยันรหัสผ่าน',
            role: 'บทบาท',
        },
        placeholders: {
            fullName: 'กรอกชื่อ-นามสกุล',
            emailAddress: 'กรอกอีเมล',
            password: 'กรอกรหัสผ่าน',
            confirmPassword: 'ยืนยันรหัสผ่าน',
            selectRole: 'เลือกบทบาท',
        },
        descriptions: {
            roleAssignment:
                'เลือกบทบาทสำหรับผู้ใช้งาน สิทธิ์การใช้งานจะถูกกำหนดให้อัตโนมัติตามบทบาท',
            permissions: 'กำหนดสิทธิ์การใช้งานเพิ่มเติมสำหรับผู้ใช้งานนี้',
        },
        labels: { selected: 'เลือกแล้ว {count}' },
        messages: {
            updated: 'อัปเดตผู้ใช้งานเรียบร้อยแล้ว',
            invalidInput: 'ข้อมูลไม่ถูกต้อง',
            error: 'เกิดข้อผิดพลาด กรุณาตรวจสอบข้อมูลในแบบฟอร์ม',
        },
    },
};
