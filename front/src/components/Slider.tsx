import Button from "@/components/Button";
import Card from "@/components/public/Card";

const Slider = (
    {
        title,
        btnText,
        cardType,
    }
    :
    {
        title: string;
        btnText: string | null,
        cardType: 'iconCard' | 'card'
    }
) => {
    return (
        <div className={"py-20"}>
            <h2 className={"text-xl text-primary mb-14 font-bold text-center"}>{title}</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 auto-rows-fr gap-5 mb-20">
                <Card className={'w-full min-h-80'} />
                <Card className={'w-full min-h-80'} />
                <Card className={'w-full min-h-80'} />
                <Card className={'w-full min-h-80'} />
            </div>
            {btnText && (
                <div className={"flex flex-row flex-wrap justify-center items-center"}>
                    <Button
                        type={'link'}
                        variant={'primary'}
                        style={'fill'}
                        href={'#'}
                        onClick={() => {}}
                        disabled={false}
                        className={'py-3 px-4 text-sm rounded'}
                    >{btnText}</Button>
                </div>
            )}
        </div>
    );
};

export default Slider;