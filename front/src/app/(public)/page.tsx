import Image from "next/image";
import Slider from "@/components/Slider";

const Home = () => {
  return (
    <>
      {/* hero section */}
        <section className={"mb-20"}>
          <div className={"w-full aspect-video relative shadow-lg rounded"}>
              <Image
                  src={'http://localhost:3000/assets/images/placeholder-image.png'}
                  alt={'university'}
                  fill={true}
                  loading={"lazy"}
                  decoding={"async"}
                  sizes={"100vw"}
                  className={"object-cover rounded shadow-lg w-full h-full"}
              />
          </div>
        </section>
        <section className={"mb-20"}>
            <div className={"grid grid-cols-1 sm:grid-cols-2 auto-rows-fr gap-20 py-20"}>
                <div>
                    <h3 className={"text-2xl font-bold mb-10"}>یک عنوان آزمایشی برای معرفی مرکز</h3>
                    <p className={"text-base leading-8 text-justify"}>
                        لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.
                    </p>
                </div>
                <div className={"h-full relative"}>
                    <Image
                        src={'http://localhost:3000/assets/images/placeholder-image.png'}
                        alt={'university'}
                        fill={true}
                        loading={"lazy"}
                        decoding={"async"}
                        sizes={"100vw"}
                        className={"object-cover rounded shadow-lg"}
                    />
                </div>
            </div>
        </section>
        <section className={"mb-20"}>
            <Slider
                title={"رشته های تحصیلی"}
                btnText={'مشاهده همه رشته ها'}
                cardType={'iconCard'}
            />
        </section>
        <section className={"mb-20"}>
            <Slider
                title={"کلاس های نمیسال دوم 1405-1406"}
                btnText={'مشاهده همه کلاس ها'}
                cardType={'iconCard'}
            />
        </section>
        <section className={"mb-20"}>
            <Slider
                title={"رویدادها"}
                btnText={'مشاهده همه رویدادها'}
                cardType={'iconCard'}
            />
        </section>
        <section>
            <Slider
                title={"مقالات اساتید و دانشجویان"}
                btnText={'مشاهده همه مقالات'}
                cardType={'iconCard'}
            />
        </section>
    </>
  );
};

export default Home;
