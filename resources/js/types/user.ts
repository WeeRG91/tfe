import { OrderStatusType } from '@/types/order';
import { PermissionType } from '@/types/permission';
import { LoyaltyPointTransactionType } from '@/types/point';
import { RoleType } from '@/types/role';

export type UserType = {
    id: number;
    name: string;
    avatar: string;
    email: string;
    email_verified_at: string;
    roles: RoleType[];
    created_at: string;
    updated_at: string;
    deleted_at: string;
};

export type EditUserType = {
    id: number;
    name: string;
    email: string;
    roles: RoleType[];
    permissions: PermissionType[];
};

export type UserDetailType = {
    id: number;
    name: string;
    email: string;
    avatar: string;
    email_verified_at: string;
    roles: RoleType[];
    permissions: PermissionType[];
    loyalty_points: LoyaltyPointTransactionType[];
    orders: {
        id: number;
        order_number: string;
        status: OrderStatusType;
        created_at: string;
        updated_at: string;
    }[];
    created_at: string;
    updated_at: string;
    deleted_at: string;
};

export type ActivateUserType = {
    id: number;
    name: string;
    email: string;
};

export enum UserFilterEnum {
    ACTIVE = 'active',
    INACTIVE = 'inactive',
}
