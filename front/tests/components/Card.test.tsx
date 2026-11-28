
import { describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { createElement, type ImgHTMLAttributes } from 'react';

import Card from '@/components/public/Card';

vi.mock('next/image', () => ({
    default: ({
                  fill,
                  ...imageProps
              }: ImgHTMLAttributes<HTMLImageElement> & { fill?: boolean }) => {
        void fill;

        return createElement('img', {
            ...imageProps,
            alt: imageProps.alt ?? '',
        });
    },
}));

describe('Card Component', () => {
    it('renders card content correctly', () => {
        render(<Card />);

        expect(screen.getByRole('article')).toBeInTheDocument();

        expect(
            screen.getByRole('heading', {
                level: 2,
                name: 'عنوان کارت',
            }),
        ).toBeInTheDocument();

        expect(
            screen.getByText(/لورم ایپسوم/),
        ).toBeInTheDocument();

        expect(
            screen.getByRole('img', {
                name: 'test',
            }),
        ).toHaveAttribute(
            'src',
            'http://localhost:3000/assets/images/placeholder-image.png',
        );

        expect(
            screen.getByRole('link', {
                name: 'بیشتر',
            }),
        ).toHaveAttribute('href', '#');
    });

    it('applies custom className to the card', () => {
        render(<Card className="custom-card" />);

        expect(
            screen.getByRole('article'),
        ).toHaveClass('custom-card');
    });
});
