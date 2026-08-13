export default {
    title: 'การชำระเงิน',
    secureCheckout: 'ชำระเงินอย่างปลอดภัย',
    completePayment: 'ดำเนินการชำระเงินให้เสร็จสิ้น',
    stripePowered: 'การชำระเงินที่ปลอดภัยผ่าน Stripe',

    order: {
        orderNumber: 'หมายเลขคำสั่งซื้อ',
        status: 'สถานะ',
        orderDetails: 'รายละเอียดคำสั่งซื้อ',
        orderType: 'ประเภทคำสั่งซื้อ',
        table: 'โต๊ะ',
        pickup: 'เวลารับอาหาร',
        customer: 'ลูกค้า',
        deliveryAddress: 'ที่อยู่จัดส่ง',
        deliveryToBms: 'จัดส่งที่ BMS',
        orderItems: 'รายการอาหาร',
    },

    item: {
        spicyLevel: 'ระดับความเผ็ด',
        noSpicy: 'ไม่เผ็ด',
        mild: 'เผ็ดน้อย',
        spicy: 'เผ็ด',
        hot: 'เผ็ดมาก',
        meat: 'เนื้อสัตว์',
        without: 'ไม่ใส่',
        notes: 'หมายเหตุ',
    },

    summary: {
        paymentSummary: 'สรุปการชำระเงิน',
        vat: 'ภาษีมูลค่าเพิ่ม {rate}%',
        totalVat: 'ภาษีมูลค่าเพิ่มทั้งหมด',
        subtotal: 'ยอดรวมก่อนสุทธิ',
        deliveryFee: 'ค่าจัดส่ง',
        discount: 'ส่วนลด',
        totalAmount: 'ยอดชำระทั้งหมด',
    },

    payment: {
        paymentDetails: 'รายละเอียดการชำระเงิน',
        paymentUnsuccessful: 'การชำระเงินไม่สำเร็จ',
        notCharged:
            'คำสั่งซื้อของคุณยังไม่ได้ถูกเรียกเก็บเงิน คุณสามารถแก้ไขข้อมูลการชำระเงินแล้วลองอีกครั้ง',
        loadingPaymentForm: 'กำลังโหลดแบบฟอร์มการชำระเงิน…',
        pay: 'ชำระ €{amount}',
        termsAgreement:
            'เมื่อดำเนินการชำระเงินต่อ ถือว่าคุณยอมรับข้อกำหนดการให้บริการของเรา',
    },

    errors: {
        initializationFailed: 'ไม่สามารถเริ่มต้นการชำระเงินได้',
        stripeLoadFailed: 'ไม่สามารถโหลด Stripe ได้',
        unableToInitialize: 'ไม่สามารถเริ่มต้นการชำระเงินได้ โปรดลองอีกครั้ง',
        paymentFailed:
            'ไม่สามารถดำเนินการชำระเงินของคุณให้เสร็จสิ้นได้ โปรดลองอีกครั้ง',
        unexpected: 'เกิดข้อผิดพลาดที่ไม่คาดคิด โปรดลองอีกครั้ง',
    },

    common: {
        notAvailable: 'ไม่มีข้อมูล',
    },
};
