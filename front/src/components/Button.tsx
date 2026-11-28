import Link from "next/link";

const Button = (
    {
        type,
        variant,
        style,
        href,
        children,
        onClick,
        disabled = false,
        className
    }
    :
    {
        type: "button" | "submit" | "link";
        variant: "primary" | "secondary" | "success" | "warning" | "danger" | "info";
        style: "fill" | "outline";
        href: string | null | undefined;
        children: React.ReactNode;
        onClick: () => void | null | undefined;
        disabled: true | false;
        className: string | null;
    }
) => {

    if (type === "link") {
        return (
            <Link
                href={href ? href : "#"}
                className={`btn-${variant}-${style} ${className} shadow-lg`}
            >{children}</Link>
        );
    } else {
        if (onClick() !== undefined) {
            return (
                <button
                    type={type}
                    className={`btn-${variant}-${style} ${className} shadow-lg`}
                    disabled={disabled}
                >{children}</button>
            );
        } else {
            return (
                <button
                    type={type}
                    className={`btn-${variant}-${style} ${className} shadow-lg`}
                    onClick={onClick}
                    disabled={disabled}
                >{children}</button>
            );
        }
    }
};

export default Button;