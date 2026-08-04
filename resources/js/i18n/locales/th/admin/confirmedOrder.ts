export default {
    title: 'คำสั่งซื้อที่ยืนยันแล้ว',
    subtitle: 'จัดการและติดตามคำสั่งซื้อตามสถานะ',
    columns: {
        confirmed: 'ยืนยันแล้ว (กำลังรอ)',
        preparing: 'กำลังเตรียม',
        ready: 'พร้อมแล้ว',
    },
    buttons: {
        completed: 'เสร็จสมบูรณ์ ({count})',
        cancelled: 'ยกเลิกแล้ว ({count})',
        refresh: 'รีเฟรช',
    },
    emptyStates: {
        confirmed: 'ไม่มีคำสั่งซื้อที่ยืนยันแล้ว',
        preparing: 'ไม่มีคำสั่งซื้อที่กำลังเตรียม',
        ready: 'ไม่มีคำสั่งซื้อที่พร้อมจัดส่ง',
        completed: 'ไม่มีคำสั่งซื้อที่เสร็จสมบูรณ์',
        cancelled: 'ไม่มีคำสั่งซื้อที่ถูกยกเลิก',
    },
    modals: {
        completedTitle: 'คำสั่งซื้อที่เสร็จสมบูรณ์',
        cancelledTitle: 'คำสั่งซื้อที่ถูกยกเลิก',
        orderCount: '{count} คำสั่งซื้อ',
    },
    messages: { updated: 'อัปเดตสถานะคำสั่งซื้อเรียบร้อยแล้ว' },
    errors: { updateFailed: 'ไม่สามารถอัปเดตสถานะคำสั่งซื้อได้' },
};
