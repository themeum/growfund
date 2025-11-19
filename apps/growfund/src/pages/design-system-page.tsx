import { zodResolver } from '@hookform/resolvers/zod';
import { BadgeCheckIcon, Loader2, XIcon } from 'lucide-react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

import { CheckboxField } from '@/components/form/checkbox-field';
import { ComboBoxField } from '@/components/form/combobox-field';
import { DatePickerField } from '@/components/form/date-picker-field';
import { GalleryField } from '@/components/form/gallery-field/gallery-field';
import { RadioField } from '@/components/form/radio-field';
import { SelectField } from '@/components/form/select-field';
import { SwitchField } from '@/components/form/switch-field';
import { TagsField } from '@/components/form/tags-field';
import { TextField } from '@/components/form/text-field';
import { TextareaField } from '@/components/form/textarea-field';
import VideoField from '@/components/form/video-field';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import Combobox from '@/components/ui/combobox';
import { Form } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { GallerySchema, VideoSchema } from '@/schemas/media';

const textColors = [
  'primary',
  'secondary',
  'tertiary',
  'success',
  'critical',
  'warning',
  'emphasis',
  'caution',
  'special',
  'special-2',
  'special-3',
  'subdued',
];

const FormSchema = z.object({
  name: z
    .string()
    .min(3, {
      message: 'Name must be at least 3 characters.',
    })
    .max(20, {
      message: 'Name should not be more than 20 characters.',
    }),
  email: z.string().email({
    message: 'This field should be a valid email.',
  }),
  age: z
    .number({
      message: 'The age field is required.',
    })
    .min(1)
    .int({
      message: 'Only integer values supported.',
    }),
  bio: z.string({ message: 'Bio field is required.' }).min(10).nullable(),
  featured: z.boolean().default(false),
  receive_email: z.boolean().default(true),
  has_2fa: z.boolean().default(true),
  tnc: z.literal(true),
  privacy_policy: z.literal(true),
  gender: z.string({ message: 'You must have to select a gender.' }),
  country: z.string({ message: 'You must select a country.' }),
  skills: z.string({ message: 'You must select a skill.' }),
  tags: z.array(z.string()).min(1, { message: 'You must select at least one tag.' }),
  user_list: z.array(z.string()).min(1, { message: 'You must select at least one user.' }),
  date: z.date({ message: 'You must select a date.' }),
  gallery: GallerySchema,
  video: VideoSchema,
});

const DesignSystemPage = () => {
  const form = useForm<z.infer<typeof FormSchema>>({
    resolver: zodResolver(FormSchema),
    defaultValues: {
      name: '',
      email: '',
      bio: '',
      featured: false,
      receive_email: true,
      has_2fa: true,
      tags: [],
      user_list: [],
      gallery: [],
    },
  });
  return (
    <div className="growfund-max-w-[75rem] growfund-mx-auto growfund-px-4 growfund-my-20">
      <div className="growfund-mb-20 growfund-flex growfund-flex-col growfund-gap-4">
        <h1 className="growfund-typo-h1 growfund-font-black growfund-text-center">Design System</h1>
        <p className="growfund-text-fg-primary growfund-text-center">
          A comprehensive collection of UI components and design patterns used throughout the
          growfund plugin. This design system ensures consistency, accessibility and maintainability
          across the entire application.
        </p>
      </div>

      <div className="growfund-grid growfund-grid-cols-2 growfund-gap-4">
        <Form {...form}>
          <form
            onSubmit={
              void form.handleSubmit((values) => {
                // eslint-disable-next-line no-console
                console.log({ values });
              })
            }
          >
            <Card>
              <CardHeader>
                <h1 className="growfund-text-2xl growfund-font-bold growfund-text-center">Form</h1>
                <p className="growfund-text-slate-500 growfund-text-center">
                  A collection of form fields and controls to demonstrate validation, error handling
                  and form submission
                </p>
              </CardHeader>
              <CardContent className="growfund-space-y-4">
                <VideoField control={form.control} name="video" label="Video" />
                <GalleryField control={form.control} name="gallery" label="Gallery" />
                <DatePickerField
                  control={form.control}
                  name="date"
                  label="Date of Birth"
                  description="Your date of birth is used to calculate your age."
                />
                <TextField
                  control={form.control}
                  name="name"
                  label="Full name"
                  placeholder="Enter name"
                  description="Please enter your full name including the middle name."
                />
                <TextField
                  control={form.control}
                  name="email"
                  label="Email address"
                  type="email"
                  placeholder="e.g. john@example.com"
                  description="Please provide a valid email address"
                />
                <TextField
                  control={form.control}
                  name="age"
                  label="Age"
                  type="number"
                  placeholder="e.g. 20"
                  description="Enter your original age."
                />
                <TextareaField
                  control={form.control}
                  name="bio"
                  label="Bio"
                  placeholder="Enter bio profile"
                  description="Write down your biography."
                />

                <TagsField
                  control={form.control}
                  name="tags"
                  label="Tags"
                  description="Select your tags"
                />

                <RadioField
                  control={form.control}
                  name="gender"
                  label="Gender"
                  description="Select your biological gender for next time preference."
                  options={[
                    { label: 'Male', value: 'male' },
                    { label: 'Female', value: 'female' },
                  ]}
                />

                <SelectField
                  control={form.control}
                  name="country"
                  label="Country"
                  description="Select your country"
                  placeholder="Select a country"
                  options={[
                    { label: 'Bangladesh', value: 'bd' },
                    { label: 'India', value: 'in' },
                    { label: 'Pakistan', value: 'pk' },
                    { label: 'Sri Lanka', value: 'lk' },
                    { label: 'Nepal', value: 'np' },
                  ]}
                />

                <ComboBoxField
                  control={form.control}
                  name="skills"
                  label="Skills"
                  description="Select your skills"
                  options={[
                    { label: 'React', value: 'react' },
                    { label: 'Vue', value: 'vue' },
                    { label: 'Angular', value: 'angular' },
                    { label: 'Svelte', value: 'svelte' },
                    { label: 'Next.js', value: 'nextjs' },
                    { label: 'Tailwind CSS', value: 'tailwind' },
                  ]}
                />

                <SwitchField
                  control={form.control}
                  name="featured"
                  label="Featured"
                  description="Make the user featured so that we could distinguish him/her separately."
                />
                <SwitchField
                  control={form.control}
                  name="receive_email"
                  label="Receive Email Notification"
                  description="Receive email notification for the user to the provided email address."
                />
                <SwitchField
                  control={form.control}
                  name="has_2fa"
                  label="Two-Factor Authentication (2FA)"
                  description="Allow Two-Factor Authentication (2FA)"
                />
                <CheckboxField
                  control={form.control}
                  name="tnc"
                  label="I've read the terms & conditions and accept them."
                />
                <CheckboxField
                  control={form.control}
                  name="privacy_policy"
                  label="I've read the privacy policies and accept them."
                  description="I've read the privacy policies and accept them."
                />
              </CardContent>
              <CardFooter className="growfund-flex growfund-gap-4 growfund-justify-end">
                <Button variant="secondary">Cancel</Button>
                <Button type="submit">Submit</Button>
              </CardFooter>
            </Card>
          </form>
        </Form>
        <Card>
          <CardHeader>
            <h1 className="growfund-text-2xl growfund-font-bold growfund-text-center">Other elements</h1>
            <p className="growfund-text-slate-500 growfund-text-center">
              A showcase of additional UI elements and components used throughout the growfund
              plugin interface.
            </p>
          </CardHeader>
          <CardContent>
            <div className="growfund-space-y-2">
              <label className="growfund-text-fg-primary growfund-font-medium">Progress (80%)</label>
              <Progress value={80} />
            </div>
          </CardContent>
        </Card>
      </div>
      <div className="growfund-grid growfund-grid-cols-2 growfund-gap-4 growfund-my-20">
        <Card>
          <CardHeader>
            <h1 className="growfund-text-2xl growfund-font-bold growfund-text-center">Form Fields</h1>
            <p className="growfund-text-slate-500 growfund-text-center">
              A collection of components that can be used in the growfund plugin.
            </p>
          </CardHeader>
          <CardContent>
            <div className="growfund-flex growfund-flex-col growfund-gap-4 growfund-mt-4">
              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <Label>Name</Label>
                <Input type="text" placeholder="e.g. John Doe" />
              </div>
              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <Label>Email</Label>
                <Input type="email" placeholder="e.g. john.doe@example.com" />
              </div>
              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <Label>Gender</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Select a gender" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="male">Male</SelectItem>
                    <SelectItem value="female">Female</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <Label>Resume</Label>
                <Input type="file" placeholder="Upload resume" />
              </div>

              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <Label>Skills</Label>
                <Combobox
                  options={[
                    {
                      label: 'React',
                      value: 'react',
                    },
                    {
                      label: 'Vue',
                      value: 'vue',
                    },
                    {
                      label: 'Angular',
                      value: 'angular',
                    },
                    {
                      label: 'Svelte',
                      value: 'svelte',
                    },
                    {
                      label: 'Next.js',
                      value: 'nextjs',
                    },
                    {
                      label: 'Tailwind CSS',
                      value: 'tailwind',
                    },
                  ]}
                />

                <div className="growfund-flex growfund-flex-wrap growfund-gap-2">
                  <Badge>React</Badge>
                  <Badge variant="secondary">Vue</Badge>
                  <Badge variant="destructive">Angular</Badge>
                  <Badge variant="outline">Svelte</Badge>
                  <Badge variant="info">Next</Badge>
                  <Badge variant="warning">Tailwind</Badge>
                  <Badge variant="special">
                    Remix <XIcon className="growfund-w-3 growfund-h-3" />
                  </Badge>
                </div>
              </div>

              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <Label>Experience Level</Label>
                <RadioGroup>
                  <div className="growfund-flex growfund-items-center growfund-space-x-2">
                    <RadioGroupItem value="entry" id="entry" />
                    <Label htmlFor="entry">Entry Level</Label>
                  </div>
                  <div className="growfund-flex growfund-items-center growfund-space-x-2">
                    <RadioGroupItem value="mid" id="mid" checked={true} />
                    <Label htmlFor="mid">Mid Level</Label>
                  </div>
                  <div className="growfund-flex growfund-items-center growfund-space-x-2">
                    <RadioGroupItem value="senior" id="senior" />
                    <Label htmlFor="senior">Senior Level</Label>
                  </div>
                </RadioGroup>
              </div>

              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <Label>Address</Label>
                <Tabs defaultValue="shipping">
                  <TabsList>
                    <TabsTrigger value="shipping">Shipping Address</TabsTrigger>
                    <TabsTrigger value="billing">Billing address</TabsTrigger>
                  </TabsList>
                  <TabsContent value="shipping">
                    <Textarea placeholder="Enter shipping address" />
                  </TabsContent>
                  <TabsContent value="billing">
                    <Textarea placeholder="Enter billing address" />
                  </TabsContent>
                </Tabs>
              </div>

              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <Label>Bio</Label>
                <Textarea placeholder="e.g. I'm a software engineer..." />
              </div>
              <div className="growfund-flex growfund-justify-between growfund-items-center growfund-gap-2">
                <Label>Featured</Label>
                <Switch />
              </div>
              <div className="growfund-flex growfund-items-center growfund-gap-2">
                <Checkbox id="terms-and-conditions" defaultChecked />
                <Label htmlFor="terms-and-conditions">
                  I've read and agree to the terms & conditions
                </Label>
              </div>
              <div className="growfund-flex growfund-items-center growfund-gap-2">
                <Checkbox id="privacy-policy" defaultChecked />
                <Label htmlFor="privacy-policy">I've read the privacy policy</Label>
              </div>

              <Separator />
            </div>
          </CardContent>
          <CardFooter className="growfund-flex growfund-gap-4 growfund-items-center growfund-justify-end">
            <Button variant="secondary">Cancel</Button>
            <Button variant="primary">Ok</Button>
          </CardFooter>
        </Card>
        <Card>
          <CardHeader>
            <h1 className="growfund-text-2xl growfund-font-bold growfund-text-center">Buttons</h1>
            <p className="growfund-text-slate-500 growfund-text-center">
              A collection of buttons that can be used in the growfund plugin.
            </p>
          </CardHeader>
          <CardContent className="growfund-flex growfund-flex-col growfund-gap-4 growfund-mt-10">
            <div className="growfund-grid growfund-grid-cols-[1fr_4fr] growfund-items-baseline">
              <h4 className="growfund-font-bold growfund-flex-shrink-0">Icon Buttons</h4>
              <div className="growfund-flex growfund-gap-4 growfund-flex-wrap growfund-mt-4">
                <Button variant="primary" size="icon">
                  <BadgeCheckIcon />
                </Button>
                <Button variant="outline" size="icon">
                  <BadgeCheckIcon />
                </Button>
                <Button variant="secondary" size="icon">
                  <BadgeCheckIcon />
                </Button>
                <Button variant="ghost" size="icon">
                  <BadgeCheckIcon />
                </Button>
                <Button variant="link" size="icon">
                  <BadgeCheckIcon />
                </Button>
                <Button variant="destructive" size="icon">
                  <BadgeCheckIcon />
                </Button>
              </div>
            </div>
            <div className="growfund-grid growfund-grid-cols-[1fr_4fr] growfund-items-baseline">
              <h4 className="growfund-font-bold growfund-flex-shrink-0">Small Buttons</h4>
              <div className="growfund-flex growfund-gap-4 growfund-flex-wrap growfund-mt-4">
                <Button size="sm" variant="primary">
                  Default Small
                </Button>
                <Button size="sm" variant="outline">
                  Outline Small
                </Button>
                <Button size="sm" variant="secondary">
                  Secondary Small
                </Button>
                <Button size="sm" variant="ghost">
                  Ghost Small
                </Button>
                <Button size="sm" variant="link">
                  Link Small
                </Button>
                <Button size="sm" variant="destructive">
                  Destructive Small
                </Button>
              </div>
            </div>
            <div className="growfund-grid growfund-grid-cols-[1fr_4fr] growfund-items-baseline">
              <h4 className="growfund-font-bold growfund-flex-shrink-0">Default Buttons</h4>
              <div className="growfund-flex growfund-gap-4 growfund-flex-wrap growfund-mt-4">
                <Button variant="primary">Default</Button>
                <Button variant="outline">Outline</Button>
                <Button variant="secondary">Secondary</Button>
                <Button variant="ghost">Ghost</Button>
                <Button variant="link">Link</Button>
                <Button variant="destructive">Destructive</Button>
              </div>
            </div>
            <div className="growfund-grid growfund-grid-cols-[1fr_4fr] growfund-items-baseline">
              <h4 className="growfund-font-bold growfund-flex-shrink-0">Large Buttons</h4>
              <div className="growfund-flex growfund-gap-4 growfund-flex-wrap growfund-mt-4">
                <Button size="lg" variant="primary">
                  Default Large
                </Button>
                <Button size="lg" variant="outline">
                  Outline Large
                </Button>
                <Button size="lg" variant="secondary">
                  Secondary Large
                </Button>
                <Button size="lg" variant="ghost">
                  Ghost Large
                </Button>
                <Button size="lg" variant="link">
                  Link Large
                </Button>
                <Button size="lg" variant="destructive">
                  Destructive Large
                </Button>
              </div>
            </div>

            <div className="growfund-grid growfund-grid-cols-[1fr_4fr] growfund-items-baseline">
              <h4 className="growfund-font-bold growfund-flex-shrink-0">Loading Buttons</h4>
              <div className="growfund-flex growfund-gap-4 growfund-flex-wrap growfund-mt-4">
                <Button variant="primary">
                  <Loader2 className="growfund-w-4 growfund-h-4 growfund-mr-2 growfund-animate-spin" />
                  Primary
                </Button>
                <Button variant="outline">
                  <Loader2 className="growfund-w-4 growfund-h-4 growfund-mr-2 growfund-animate-spin" />
                  Outline
                </Button>
                <Button variant="secondary">
                  <Loader2 className="growfund-w-4 growfund-h-4 growfund-mr-2 growfund-animate-spin" />
                  Secondary
                </Button>
                <Button variant="ghost">
                  <Loader2 className="growfund-w-4 growfund-h-4 growfund-mr-2 growfund-animate-spin" />
                  Ghost
                </Button>
                <Button variant="link">
                  <Loader2 className="growfund-w-4 growfund-h-4 growfund-mr-2 growfund-animate-spin" />
                  Link
                </Button>
                <Button variant="destructive">
                  <Loader2 className="growfund-w-4 growfund-h-4 growfund-mr-2 growfund-animate-spin" />
                  Destructive
                </Button>
              </div>
            </div>

            <div className="growfund-grid growfund-grid-cols-[1fr_4fr] growfund-items-baseline">
              <h4 className="growfund-font-bold growfund-flex-shrink-0">Disabled Buttons</h4>
              <div className="growfund-flex growfund-gap-4 growfund-flex-wrap growfund-mt-4">
                <Button variant="primary" disabled>
                  Default
                </Button>
                <Button variant="outline" disabled>
                  Outline
                </Button>
                <Button variant="secondary" disabled>
                  Secondary
                </Button>
                <Button variant="ghost" disabled>
                  Ghost
                </Button>
                <Button variant="link" disabled>
                  Link
                </Button>
                <Button variant="destructive" disabled>
                  Destructive
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
      <Card>
        <CardHeader>
          <h1 className="growfund-text-2xl growfund-font-bold growfund-text-center">Typography</h1>
          <p className="growfund-text-center growfund-text-fg-muted">
            A collection of typography that can be used in the growfund plugin.
          </p>
        </CardHeader>
        <CardContent>
          <div className="growfund-flex growfund-flex-col growfund-gap-4">
            {textColors.map((color, index) => (
              <div key={index}>
                <Badge variant="info" className="growfund-uppercase growfund-mb-2">
                  {color}
                </Badge>
                <h1 className={`growfund-typo-h1 growfund-text-fg-${color}`}>
                  A quick brown fox jumps over the lazy dog
                </h1>
                <h2 className={`growfund-typo-h2 growfund-text-fg-${color}`}>
                  A quick brown fox jumps over the lazy dog
                </h2>
                <h3 className={`growfund-typo-h3 growfund-text-fg-${color}`}>
                  A quick brown fox jumps over the lazy dog
                </h3>
                <h4 className={`growfund-typo-h4 growfund-text-fg-${color}`}>
                  A quick brown fox jumps over the lazy dog
                </h4>
                <h5 className={`growfund-typo-h5 growfund-text-fg-${color}`}>
                  A quick brown fox jumps over the lazy dog
                </h5>
                <h6 className={`growfund-typo-h6 growfund-text-fg-${color}`}>
                  A quick brown fox jumps over the lazy dog
                </h6>
                <p className={`growfund-typo-pargrowfund-agraph text-fg-${color}`}>
                  A quick brown fox jumps over the lazy dog
                </p>
                <p className={`growfund-typo-smagrowfund-ll text-fg-${color}`}>
                  A quick brown fox jumps over the lazy dog
                </p>
                <p className={`growfund-typo-tingrowfund-y text-fg-${color}`}>
                  A quick brown fox jumps over the lazy dog
                </p>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default DesignSystemPage;
