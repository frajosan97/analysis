import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import {
    Navbar,
    Container,
    Nav,
    NavDropdown,
    Offcanvas,
} from 'react-bootstrap';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;
    const [showMobileMenu, setShowMobileMenu] = useState(false);

    const handleCloseMobileMenu = () => setShowMobileMenu(false);
    const handleShowMobileMenu = () => setShowMobileMenu(true);

    return (
        <div className="min-h-screen bg-light">
            <Navbar bg="white" expand="lg" className="border-bottom">
                <Container fluid="lg">
                    <Navbar.Brand as={Link} href="/">
                        <ApplicationLogo style={{ maxHeight: "100px" }} />
                    </Navbar.Brand>

                    <div className="d-flex align-items-center">
                        <Navbar.Toggle
                            aria-controls="mobile-nav-menu"
                            onClick={handleShowMobileMenu}
                            className="d-lg-none border-0"
                        >
                            <span className="navbar-toggler-icon"></span>
                        </Navbar.Toggle>

                        <Navbar.Collapse id="basic-navbar-nav" className="d-none d-lg-flex">
                            <Nav className="me-auto">
                                <Nav.Link
                                    as={Link}
                                    href={route('dashboard')}
                                    active={route().current('dashboard')}
                                >
                                    Dashboard
                                </Nav.Link>
                                <Nav.Link
                                    as={Link}
                                    href={route('exams.index')}
                                    active={route().current('exams.index')}
                                >
                                    Exams
                                </Nav.Link>
                            </Nav>

                            <Nav>
                                <NavDropdown
                                    title={
                                        <>
                                            {user.name}
                                            <i className="ms-2 fas fa-caret-down"></i>
                                        </>
                                    }
                                    align="end"
                                    id="user-dropdown"
                                >
                                    <NavDropdown.Item as={Link} href={route('profile.edit')}>
                                        <i className="fas fa-user me-2"></i> Profile
                                    </NavDropdown.Item>
                                    <NavDropdown.Divider />
                                    <NavDropdown.Item
                                        as={Link}
                                        href={route('logout')}
                                        method="post"
                                    >
                                        <i className="fas fa-sign-out-alt me-2"></i> Log Out
                                    </NavDropdown.Item>
                                </NavDropdown>
                            </Nav>
                        </Navbar.Collapse>
                    </div>
                </Container>
            </Navbar>

            {/* Mobile Menu Offcanvas */}
            <Offcanvas
                show={showMobileMenu}
                onHide={handleCloseMobileMenu}
                placement="end"
                className="d-lg-none"
            >
                <Offcanvas.Header closeButton>
                    <Offcanvas.Title>Menu</Offcanvas.Title>
                </Offcanvas.Header>
                <Offcanvas.Body>
                    <Nav className="flex-column">
                        <Nav.Link
                            as={Link}
                            href={route('dashboard')}
                            active={route().current('dashboard')}
                            onClick={handleCloseMobileMenu}
                        >
                            Dashboard
                        </Nav.Link>
                        <Nav.Link
                            as={Link}
                            href={route('exams.index')}
                            active={route().current('exams.index')}
                            onClick={handleCloseMobileMenu}
                        >
                            Exams
                        </Nav.Link>

                        <div className="border-top mt-3 pt-3">
                            <div className="px-3">
                                <div className="fw-bold text-dark">{user.name}</div>
                                <div className="text-muted small">{user.email}</div>
                            </div>

                            <Nav className="flex-column mt-2">
                                <Nav.Link
                                    as={Link}
                                    href={route('profile.edit')}
                                    onClick={handleCloseMobileMenu}
                                >
                                    <i className="fas fa-user me-2"></i> Profile
                                </Nav.Link>
                                <Nav.Link
                                    as={Link}
                                    href={route('logout')}
                                    method="post"
                                    onClick={handleCloseMobileMenu}
                                >
                                    <i className="fas fa-sign-out-alt me-2"></i> Log Out
                                </Nav.Link>
                            </Nav>
                        </div>
                    </Nav>
                </Offcanvas.Body>
            </Offcanvas>

            {header && (
                <header className="bg-white shadow-sm">
                    <Container fluid="lg" className="py-3">
                        <h1 className="h4 mb-0">{header}</h1>
                    </Container>
                </header>
            )}

            <main className="py-4">
                <Container fluid="lg">{children}</Container>
            </main>
        </div>
    );
}