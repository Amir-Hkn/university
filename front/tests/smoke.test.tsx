import { describe, expect, it } from 'vitest';
import { render, screen } from '@testing-library/react';

function TestingEnvironmentProbe() {
    return (
        <main>
            <h1>Frontend Testing Environment</h1>
            <p>Testing infrastructure is working.</p>
        </main>
    );
}

describe('Frontend Testing Infrastructure', () => {
    it('renders React components successfully', () => {
        render(<TestingEnvironmentProbe />);

        expect(
            screen.getByRole('heading', {
                name: 'Frontend Testing Environment',
            }),
        ).toBeInTheDocument();

        expect(
            screen.getByText('Testing infrastructure is working.'),
        ).toBeInTheDocument();
    });
});