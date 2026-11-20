import Header from "@/components/public/header";
import Footer from "@/components/public/footer";

const PuiblicRootTemplate = ({ children }: { children: React.ReactNode }) => {
    return (
        <div className={"min-h-dvh grid grid-rows-[auto_1fr_auto]"}>
            <Header />
            <main>
                <div className={"container mx-auto px-4"}>
                    {children}
                </div>
            </main>
            <Footer />
        </div>
    );
};

export default PuiblicRootTemplate;