<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import allergen from '@/routes/allergen';
import { AllergenType } from '@/types/allergen';
import { SquarePenIcon, X } from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
    selectedAllergen: AllergenType | null;
}>();
</script>

<template>
    <Dialog :open="props.open" @update:open="props.onClose">
        <DialogContent class="max-w-2xl">
            <DialogTitle class="flex items-center gap-2">
                <div class="flex items-center">
                    <img
                        v-if="props.selectedAllergen?.image"
                        :src="props.selectedAllergen?.image"
                        :alt="props.selectedAllergen?.name"
                        class="h-12 w-12 cursor-pointer object-cover"
                    />
                </div>
                <span>{{ props.selectedAllergen?.name }}</span>
            </DialogTitle>
            <DialogDescription>
                {{ props.selectedAllergen?.description }}
            </DialogDescription>

            <div class="flex items-center gap-2">
                <Badge
                    v-for="ingredient in props.selectedAllergen?.ingredients"
                    :key="ingredient.id"
                >
                    {{ ingredient.name }}
                </Badge>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <Button variant="outline" @click="props.onClose"
                    ><X /> Close</Button
                >
                <Button
                    ><a
                        :href="allergen.edit(props.selectedAllergen?.id!).url"
                        class="flex gap-2"
                    >
                        <SquarePenIcon /> Edit
                    </a></Button
                >
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
