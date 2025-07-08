import { Form, Button, Container, Row, Col, Card, Stack } from 'react-bootstrap';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Register" />

            <Container className="py-4">
                <Row className="justify-content-center">
                    <Col md={8} lg={6}>
                        <Card className="p-4">
                            <Card.Body>
                                <Form onSubmit={submit}>
                                    <Form.Group controlId="name" className="mb-3">
                                        <Form.Label>Name</Form.Label>
                                        <Form.Control
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            autoComplete="name"
                                            autoFocus
                                            isInvalid={!!errors.name}
                                            required
                                        />
                                        <Form.Control.Feedback type="invalid">
                                            {errors.name}
                                        </Form.Control.Feedback>
                                    </Form.Group>

                                    <Form.Group controlId="email" className="mb-3">
                                        <Form.Label>Email</Form.Label>
                                        <Form.Control
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            autoComplete="username"
                                            isInvalid={!!errors.email}
                                            required
                                        />
                                        <Form.Control.Feedback type="invalid">
                                            {errors.email}
                                        </Form.Control.Feedback>
                                    </Form.Group>

                                    <Form.Group controlId="password" className="mb-3">
                                        <Form.Label>Password</Form.Label>
                                        <Form.Control
                                            type="password"
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                            autoComplete="new-password"
                                            isInvalid={!!errors.password}
                                            required
                                        />
                                        <Form.Control.Feedback type="invalid">
                                            {errors.password}
                                        </Form.Control.Feedback>
                                    </Form.Group>

                                    <Form.Group controlId="password_confirmation" className="mb-3">
                                        <Form.Label>Confirm Password</Form.Label>
                                        <Form.Control
                                            type="password"
                                            value={data.password_confirmation}
                                            onChange={(e) => setData('password_confirmation', e.target.value)}
                                            autoComplete="new-password"
                                            isInvalid={!!errors.password_confirmation}
                                            required
                                        />
                                        <Form.Control.Feedback type="invalid">
                                            {errors.password_confirmation}
                                        </Form.Control.Feedback>
                                    </Form.Group>

                                    <Stack direction="horizontal" gap={3} className="justify-content-between">
                                        <Link
                                            href={route('login')}
                                            className="text-decoration-none"
                                        >
                                            Already registered?
                                        </Link>
                                        <Button
                                            variant="primary"
                                            type="submit"
                                            disabled={processing}
                                            className="ms-auto"
                                        >
                                            {processing ? 'Registering...' : 'Register'}
                                        </Button>
                                    </Stack>
                                </Form>
                            </Card.Body>
                        </Card>
                    </Col>
                </Row>
            </Container>
        </GuestLayout>
    );
}