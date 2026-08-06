export default {
    title: 'ค้นหาทั่วทั้งระบบ',
    description: 'คุณกำลังค้นหาอะไร?',
    resultTypes: {
        dishes: 'เมนูอาหาร',
        drinks: 'เครื่องดื่ม',
        ingredients: 'วัตถุดิบ',
        meats: 'เนื้อสัตว์',
        allergens: 'สารก่อภูมิแพ้',
        roles: 'บทบาท',
        users: 'ผู้ใช้งาน',
    },
    placeholders: { search: 'ค้นหาเมนูอาหาร เครื่องดื่ม วัตถุดิบ...' },
    messages: {
        startTyping: 'เริ่มพิมพ์เพื่อค้นหา…',
        noResults: 'ไม่พบผลลัพธ์',
        resultsFound: 'พบ {count} ผลลัพธ์',
    },
    keyboard: {
        navigate: 'เพื่อเลื่อนดู',
        select: 'เพื่อเลือก',
        close: 'เพื่อปิด',
    },
};
