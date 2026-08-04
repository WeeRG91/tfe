export default {
    title: 'วัตถุดิบ',
    allAllergens: 'สารก่อภูมิแพ้ทั้งหมด',
    table: {
        name: 'ชื่อ',
        allergen: 'สารก่อภูมิแพ้',
        createdAt: 'บันทึกเมื่อ',
        updatedAt: 'แก้ไขเมื่อ',
        deletedAt: 'ลบเมื่อ',
    },
    messages: {
        noIngredient: 'ยังไม่มีวัตถุดิบ',
        confirmRestore: 'คุณแน่ใจหรือไม่ว่าต้องการกู้คืนวัตถุดิบนี้?',
        confirmDelete: 'คุณแน่ใจหรือไม่ว่าต้องการลบวัตถุดิบนี้อย่างถาวร?',
        confirmMoveToBin:
            'คุณแน่ใจหรือไม่ว่าต้องการย้ายวัตถุดิบนี้ไปยังถังขยะ?',
    },
    errors: {
        loadFailed: 'ไม่สามารถโหลดวัตถุดิบเพิ่มเติมได้',
        binFailed: 'ไม่สามารถย้ายวัตถุดิบไปยังถังขยะได้',
        restoreFailed: 'ไม่สามารถกู้คืนวัตถุดิบได้',
        deleteFailed: 'ไม่สามารถลบวัตถุดิบได้',
    },
    form: {
        title: 'สร้างวัตถุดิบ',
        editTitle: "แก้ไขวัตถุดิบ: {name}",
        breadcrumbs: {
            ingredients: 'วัตถุดิบ',
            create: 'บันทึก',
            edit: 'แก้ไข',
        },
        sections: {
            information: 'ข้อมูลวัตถุดิบ',
            photos: 'รูปภาพ',
        },
        fields: {
            name: 'ชื่อ',
            description: 'รายละเอียด',
            allergen: 'สารก่อภูมิแพ้',
        },
        messages: {
            created: 'สร้างวัตถุดิบเรียบร้อยแล้ว',
            updated: 'อัปเดตวัตถุดิบเรียบร้อยแล้ว',
            error: 'เกิดข้อผิดพลาด กรุณาตรวจสอบข้อมูลในแบบฟอร์ม',
        },
    },
    createModal: {
        title: 'สร้างวัตถุดิบ',
        description: 'กรอกข้อมูลด้านล่างเพื่อสร้างวัตถุดิบใหม่',
        fields: {
            name: 'ชื่อ',
            description: 'รายละเอียด',
            allergen: 'สารก่อภูมิแพ้',
        },
        error: 'เกิดข้อผิดพลาด โปรดตรวจสอบข้อมูลในแบบฟอร์ม',
    },
};
