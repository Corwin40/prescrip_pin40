<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Member;
use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Prescription;
use App\Form\Admin\MemberResetPasswordType;
use App\Form\Admin\MemberType;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;


#[Route('/admin/member')]
final class MemberController extends AbstractController
{
    #[Route('/', name: 'app_admin_member_index', methods: ['GET'])]
    public function index(MemberRepository $memberRepository): Response
    {
        $user = $this->getUser();

        if($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
            return $this->render('admin/member/index.html.twig', [
                'members' => $memberRepository->findAll(),
            ]);
        }

        if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
            return $this->render('admin/member/index.html.twig', [
                'members' => $memberRepository->findBy(['referent' => $user]),
            ]);
        }
        return $this->redirectToRoute('app_admin_dashboard_index');
    }

    #[Route('/new/{role}', name: 'app_admin_member_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger,
        $role
    ): Response {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();

            $firstname = $form->get('firstname')->getData();
            $lastname = $form->get('lastname')->getData();
            if($firstname || $lastname){
                $name = $firstname . ' ' . $lastname;
                $member->setSlug($slugger->slug($name, '')->lower());
            }

            $member->setIsVerified(1);

            if ($role === 'prescripteur') {
                $member->setRoles(['ROLE_PRESCRIPTEUR']);
            }else if($role === 'mediateur'){
                $member->setRoles(['ROLE_MEDIATEUR']);
            }else if($role === 'admin'){
                $member->setRoles(['ROLE_ADMIN']);
            }
            if ($password) {
                $hashedPassword = $passwordHasher->hashPassword($member, $password);
                $member->setPassword($hashedPassword);
            } else {
                return $this->render('admin/member/new.html.twig', [
                    'member' => $member,
                    'form' => $form,
                ]);
            }

            $entityManager->persist($member);
            $entityManager->flush();

            $this->addFlash('success', 'Le membre a bien été créé.');
            return $this->redirectToRoute('app_admin_member_index');
        }

        return $this->render('admin/member/new.html.twig', [
            'role' => $role,
            'member' => $member,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_member_show', methods: ['GET'])]
    public function show(Member $member): Response
    {
        return $this->render('admin/member/show.html.twig', [
            'member' => $member,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_member_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Member $member,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $firstname = $form->get('firstname')->getData();
            $lastname = $form->get('lastname')->getData();
            if($firstname || $lastname){
                $name = $firstname . ' ' . $lastname;
                $member->setSlug($slugger->slug($name, '')->lower());
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le membre a bien été mis à jour.');
            return $this->redirectToRoute('app_admin_member_index');
        }

        return $this->render('admin/member/edit.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    #[Route("/renew-password/{id}", name: "app_admin_member_renew_password", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_SUPER_ADMIN")]
    public function renewPassword(
        Request $request,
        Member $member,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        UserPasswordHasherInterface $passwordHasher
    )
    {
        $form = $this->createForm(MemberResetPasswordType::class, $member, [
            'action' => $this->generateUrl('app_admin_member_renew_password', ['id' => $member->getId()]),
            'method' => 'POST',
            'attr' => [
                'id' => 'form_ResetPassword',
            ]
        ]);
        $form->handleRequest($request);

        //dd($form);

        if ($form->isSubmitted() && $form->isValid()) {
            //dd($form->get('password')->getData());

            $password = $form->get('password')->getData();

            if ($password) {
                $hashedPassword = $passwordHasher->hashPassword($member, $password);
                $member->setPassword($hashedPassword);
            }

            $entityManager->persist($member);
            $entityManager->flush();

            return $this->json([
                'code' => 200,
                'message' => 'Mot de passe actualisé'
            ],200);
        }

        $view = $this->render('admin/member/_formResetPassword.html.twig', [
            'form' => $form,
        ]);

        return $this->json([
            'code' => 200,
            'formView' => $view->getContent(),
        ],200);
    }

    #[Route('/{id}', name: 'app_admin_member_delete', methods: ['POST'])]
    public function delete(Request $request, Member $member, EntityManagerInterface $entityManager, Beneficiary $beneficiary, Prescription $prescription): Response
    {
        if ($this->isCsrfTokenValid('delete' . $member->getId(), $request->getPayload()->getString('_token'))) {
            if($member->getRoles()[0] === 'ROLE_PRESCRIPTEUR')
            {
                $beneficiaries = $member->getBeneficiaries();
                foreach($beneficiaries as $beneficiary)
                {
                    $beneficiary->setPrescriptor(null);
                    $entityManager->persist($beneficiary);
                }
                $prescriptions = $member->getPrescriptions();
                foreach($prescriptions as $prescription){
                    $prescription->setMembre(null);
                    $entityManager->persist($prescription);
                }
            }

            $entityManager->remove($member);
            $entityManager->flush();
            $this->addFlash('danger', 'Le membre a bien été supprimé.');
        }

        return $this->redirectToRoute('app_admin_member_index');
    }
}
