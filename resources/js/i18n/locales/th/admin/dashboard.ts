export default {
    title: 'แดชบอร์ด',
    period: 'ช่วงเวลา',
    periods: {
        today: 'วันนี้',
        last7: '7 วันที่ผ่านมา รวมวันนี้',
        last30: '30 วันที่ผ่านมา รวมวันนี้',
        last90: '90 วันที่ผ่านมา รวมวันนี้',
    },
    periodOptions: {
        today: 'วันนี้',
        last7: '7 วันที่ผ่านมา',
        last30: '30 วันที่ผ่านมา',
        last90: '90 วันที่ผ่านมา',
    },
    cards: {
        completedOrders: 'คำสั่งซื้อที่เสร็จสมบูรณ์',
        paidSales: 'ยอดขายที่ชำระเงินและเสร็จสมบูรณ์',
        averageOrderValue: 'มูลค่าเฉลี่ยต่อคำสั่งซื้อที่ชำระเงิน',
        cancellationRate: 'อัตราการยกเลิก',
        preparationTime: 'เวลาเตรียมอาหารเฉลี่ย',
        returningCustomers: 'ลูกค้าที่กลับมาสั่งซื้อและชำระเงิน',
    },
    details: {
        cancellation: 'ยกเลิก {cancelled} จาก {total} คำสั่งซื้อ · {period}',
        preparationSample:
            'คำนวณจาก {count} คำสั่งซื้อที่เสร็จสมบูรณ์ · {period}',
        noTimedOrders:
            'ไม่มีคำสั่งซื้อที่เสร็จสมบูรณ์พร้อมข้อมูลเวลา · {period}',
        returningCustomers:
            'ลูกค้าที่กลับมา {returning} จาก {total} คน · {period}',
        noPayingCustomers:
            'ไม่มีลูกค้าที่มีคำสั่งซื้อที่ชำระเงินและเสร็จสมบูรณ์ · {period}',
    },
    units: {
        minutes: 'นาที',
    },
    charts: {
        dailySales: {
            title: 'ยอดขายรายวันที่ชำระเงินและเสร็จสมบูรณ์',
            seriesName: 'ยอดขายที่ชำระเงินและเสร็จสมบูรณ์',
            detail: 'รวมภาษีมูลค่าเพิ่มแล้ว',
        },
        topDishes: {
            title: 'เมนูขายดี',
            tooltip: 'ขายได้ {count} รายการ',
            series: 'จำนวนที่ขายได้',
            detail: 'จัดอันดับตามจำนวนที่ขายได้',
            empty: 'ไม่มีรายการเมนูที่ชำระเงินและเสร็จสมบูรณ์ในช่วงเวลานี้',
        },
        ordersByType: {
            title: 'คำสั่งซื้อแยกตามประเภท',
            tooltip: '{type}: {count} คำสั่งซื้อ ({percent}%)',
            seriesName: 'คำสั่งซื้อ',
            detail: 'รวมคำสั่งซื้อที่ถูกยกเลิก',
            empty: 'ไม่มีคำสั่งซื้อในช่วงเวลานี้',
        },
        ordersByHour: {
            title: 'คำสั่งซื้อแยกตามชั่วโมง',
            tooltip: '{count} คำสั่งซื้อ',
            seriesName: 'คำสั่งซื้อ',
            detail: 'ตามเวลาท้องถิ่นของร้านอาหาร',
            empty: 'ไม่มีคำสั่งซื้อในช่วงเวลานี้',
        },
    },
};
