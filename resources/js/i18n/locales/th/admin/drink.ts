export default {
    title: 'เครื่องดื่ม',
    categories: {
        all: 'ทั้งหมด',
        softDrink: 'เครื่องดื่มไม่มีแอลกอฮอล์',
        hotDrink: 'เครื่องดื่มร้อน',
        smoothie: 'สมูทตี้',
        beer: 'เบียร์',
        wine: 'ไวน์',
        cocktail: 'ค็อกเทล',
        mocktail: 'ม็อกเทล',
    },
    table: {
        name: 'ชื่อ',
        category: 'หมวดหมู่',
        price: 'ราคา',
        availability: 'สถานะ',
        createdAt: 'บันทึกเมื่อ',
        updatedAt: 'แก้ไขเมื่อ',
        deletedAt: 'ลบเมื่อ',
    },
    messages: {
        noDrink: 'ยังไม่มีเครื่องดื่ม',
        confirmRestore: 'คุณแน่ใจหรือไม่ว่าต้องการกู้คืนเครื่องดื่มนี้?',
        confirmDelete: 'คุณแน่ใจหรือไม่ว่าต้องการลบเครื่องดื่มนี้อย่างถาวร?',
        confirmMoveToBin:
            'คุณแน่ใจหรือไม่ว่าต้องการย้ายเครื่องดื่มนี้ไปยังถังขยะ?',
        confirmAvailable:
            'คุณแน่ใจหรือไม่ว่าต้องการทำให้เครื่องดื่มนี้พร้อมจำหน่าย?',
        confirmUnavailable:
            'คุณแน่ใจหรือไม่ว่าต้องการทำให้เครื่องดื่มนี้ไม่พร้อมจำหน่าย?',
    },
    errors: {
        loadFailed: 'ไม่สามารถโหลดรายการเครื่องดื่มเพิ่มเติมได้',
        availabilityFailed: 'ไม่สามารถอัปเดตสถานะของเครื่องดื่มได้',
        binFailed: 'ไม่สามารถย้ายเครื่องดื่มไปยังถังขยะได้',
        restoreFailed: 'ไม่สามารถกู้คืนเครื่องดื่มได้',
        deleteFailed: 'ไม่สามารถลบเครื่องดื่มได้',
    },
    form: {
        title: 'สร้างเครื่องดื่ม',
        editTitle: 'แก้ไขเครื่องดื่ม: {name}',
        breadcrumbs: {
            drinks: 'เครื่องดื่ม',
            create: 'บันทึก',
            edit: 'แก้ไข',
        },
        sections: {
            information: 'ข้อมูลเครื่องดื่ม',
            photos: 'รูปภาพ',
        },
        fields: {
            name: 'ชื่อ',
            description: 'รายละเอียด',
            price: 'ราคา (€)',
            category: 'หมวดหมู่',
        },
        messages: {
            created: 'สร้างเครื่องดื่มเรียบร้อยแล้ว',
            edited: 'แก้ไขเครื่องดื่มเรียบร้อยแล้ว',
            error: 'เกิดข้อผิดพลาด โปรดตรวจสอบข้อมูลในแบบฟอร์ม',
        },
    },
};
