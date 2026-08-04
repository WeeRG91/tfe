export default {
    title: 'บทบาท',
    subtitle: 'จัดการบทบาทและสิทธิ์การใช้งาน',
    permissions: {
        label: '{count} สิทธิ์',
        title: 'สิทธิ์การใช้งาน',
        noPermissions: 'ยังไม่ได้กำหนดสิทธิ์การใช้งาน',
        uncategorized: 'ไม่มีหมวดหมู่',
    },
    dates: { updated: 'อัปเดตเมื่อ: {date}' },
    messages: {
        loadMore: 'โหลดบทบาทเพิ่มเติม',
        noSearchResults: 'ไม่พบบทบาทที่ตรงกับ “{query}”',
        confirmDelete: 'คุณแน่ใจหรือไม่ว่าต้องการลบบทบาทนี้อย่างถาวร?',
    },
    errors: {
        loadFailed: 'ไม่สามารถโหลดบทบาทได้',
        deleteFailed: 'ไม่สามารถลบบทบาทได้',
    },
    form: {
        createTitle: 'สร้างบทบาท',
        editTitle: 'แก้ไขบทบาท',
        sections: {
            roleInformation: 'ข้อมูลบทบาท',
            permissions: 'สิทธิ์การใช้งาน',
        },
        fields: { roleName: 'ชื่อบทบาท' },
        placeholders: { roleName: 'กรอกชื่อบทบาท เช่น ผู้แก้ไข ผู้จัดการ' },
        descriptions: { permissions: 'เลือกสิทธิ์การใช้งานสำหรับบทบาทนี้' },
        labels: { selected: 'เลือกแล้ว {count}' },
        messages: {
            created: 'สร้างบทบาทเรียบร้อยแล้ว',
            edited: 'อัปเดตบทบาทเรียบร้อยแล้ว',
            invalidInput: 'ข้อมูลไม่ถูกต้อง',
            error: 'เกิดข้อผิดพลาด กรุณาตรวจสอบข้อมูลในแบบฟอร์ม',
        },
    },
};
