export default {
    title: 'อาหาร',
    categories: {
        all: 'ทั้งหมด',
        appetizer: 'อาหารเรียกน้ำย่อย',
        mainCourse: 'อาหารจานหลัก',
        soup: 'เมนูแกงและต้ม',
        noodles: 'เมนูเส้น',
        dessert: 'ของหวาน',
        vegetarian: 'อาหารมังสวิรัติ',
    },
    spicyLevel: {
        noSpicy: 'ไม่เผ็ด',
        mild: 'เผ็ดน้อย',
        spicy: 'เผ็ด',
        hot: 'เผ็ดมาก',
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
        noDish: 'ยังไม่มีรายการอาหาร',
        confirmRestore: 'คุณแน่ใจหรือไม่ว่าต้องการกู้คืนอาหารนี้?',
        confirmDelete: 'คุณแน่ใจหรือไม่ว่าต้องการลบอาหารนี้อย่างถาวร?',
        confirmBin: 'คุณแน่ใจหรือไม่ว่าต้องการย้ายอาหารนี้ไปยังถังขยะ?',
        confirmAvailable: 'คุณแน่ใจหรือไม่ว่าต้องการทำให้อาหารนี้พร้อมจำหน่าย?',
        confirmUnavailable:
            'คุณแน่ใจหรือไม่ว่าต้องการทำให้อาหารนี้ไม่พร้อมจำหน่าย?',
    },
    errors: {
        loadFailed: 'ไม่สามารถโหลดรายการอาหารเพิ่มเติมได้',
        availabilityFailed: 'ไม่สามารถอัปเดตสถานะของอาหารได้',
        binFailed: 'ไม่สามารถย้ายอาหารไปยังถังขยะได้',
        restoreFailed: 'ไม่สามารถกู้คืนอาหารได้',
        deleteFailed: 'ไม่สามารถลบอาหารได้',
    },
    form: {
        title: 'สร้างเมนูอาหาร',
        breadcrumbs: {
            dishes: 'อาหาร',
            create: 'บันทึก',
            edit: 'แก้ไข',
        },
        sections: {
            information: 'ข้อมูลอาหาร',
            photos: 'รูปภาพ',
        },
        fields: {
            name: 'ชื่อ',
            description: 'รายละเอียด',
            price: 'ราคา (€)',
            category: 'หมวดหมู่',
            ingredients: 'วัตถุดิบ',
            meatOptions: 'ประเภทเนื้อ',
            defaultSpicyLevel: 'เลือกระดับความเผ็ดเริ่มต้นของเมนูนี้',
        },
        messages: {
            created: 'สร้างเมนูอาหารเรียบร้อยแล้ว',
            edited: 'แก้ไขเมนูเรียบร้อยแล้ว',
            error: 'เกิดข้อผิดพลาด โปรดตรวจสอบข้อมูลในแบบฟอร์ม',
        },
    },
};
