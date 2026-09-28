import fs from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';


const sourceDirectory = path.resolve('public/images');
const outputDirectory = path.resolve('public/images/home');

const sliderDirectory = path.join(outputDirectory, 'slider');
const thumbnailDirectory = path.join(outputDirectory, 'thumbnails');
const introDirectory = path.join(outputDirectory, 'intro');

await Promise.all([
    fs.mkdir(sliderDirectory, { recursive: true }),
    fs.mkdir(thumbnailDirectory, { recursive: true }),
    fs.mkdir(introDirectory, { recursive: true }),
]);

const imageNames = Array.from(
    {length: 15},
    (_, index) => `dish-${index + 1}`,
)

for (const imageName of imageNames) {
    const source = path.join(sourceDirectory, `${imageName}.jpg`);

    await sharp(source)
        .rotate()
        .resize(480, 480, {
            fit: 'cover',
            position: 'attention',
            withoutEnlargement: true,
        })
        .webp({
            quality: 72,
            effort: 5,
        })
        .toFile(path.join(introDirectory, `${imageName}.webp`))

    const imageNumber = Number(imageName.replace('dish-', ''));

    if (imageNumber <= 5) {
        await sharp(source)
            .rotate()
            .resize(1600, 1100, {
                fit: 'cover',
                position: 'attention',
                withoutEnlargement: true,
            })
            .webp({
                quality: 80,
                effort: 5,
            })
            .toFile(path.join(sliderDirectory, `${imageName}.webp`));

        await sharp(source)
            .rotate()
            .resize(320, 200, {
                fit: 'cover',
                position: 'attention',
                withoutEnlargement: true,
            })
            .webp({
                quality: 70,
                effort: 5,
            })
            .toFile(path.join(thumbnailDirectory, `${imageName}.webp`));
    }

    console.log(`Optimized ${imageName}`);
}

console.log(`Images written to ${outputDirectory}`);
