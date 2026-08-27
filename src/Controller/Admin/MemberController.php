<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Member;
use App\Form\Admin\MemberResetPasswordType;
use App\Form\Admin\MemberType;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;


final class MemberController extends AbstractController
{
    /**
     * Liste des membres regroupés par structure.
     * Un ROLE_SUPER_ADMIN voit tous les membres sauf les autres super-admins ;
     * un ROLE_MEDIATEUR ne voit que les membres dont il est le référent.
     * Tout autre rôle est redirigé vers le dashboard (accès refusé implicite).
     */
    #[Route('/admin/member/', name: 'app_admin_member_index', methods: ['GET'])]
    public function index(MemberRepository $memberRepository): Response
    {
        $user = $this->getUser();

        if($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
            return $this->render('admin/member/index.html.twig', [
                'membersByStructure' => $this->groupByStructure($memberRepository->findAllExceptRole('ROLE_SUPER_ADMIN')),
            ]);
        }

        if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
            return $this->render('admin/member/index.html.twig', [
                'membersByStructure' => $this->groupByStructure($memberRepository->findBy(['referent' => $user])),
            ]);
        }
        return $this->redirectToRoute('app_admin_dashboard_index');
    }

    /**
     * Regroupe les membres par structure (nom de structure trié alphabétiquement),
     * les membres sans structure sont placés dans un groupe "Sans structure" en fin de liste.
     *
     * @param iterable<Member> $members
     * @return array<string, Member[]>
     */
    private function groupByStructure(iterable $members): array
    {
        $grouped = [];
        $withoutStructure = [];

        foreach ($members as $member) {
            $structure = $member->getStructure();
            if ($structure) {
                $grouped[$structure->getName()][] = $member;
            } else {
                $withoutStructure[] = $member;
            }
        }

        ksort($grouped, SORT_NATURAL | SORT_FLAG_CASE);

        if ($withoutStructure) {
            $grouped['Sans structure'] = $withoutStructure;
        }

        return $grouped;
    }

    /**
     * Création d'un membre pour un rôle donné dans l'URL ({role} = prescripteur|mediateur|admin).
     * Le mot de passe est obligatoire : si le formulaire est valide mais sans mot de passe,
     * la vue est réaffichée sans persister le membre.
     */
    #[Route('/admin/member/new/{role}', name: 'app_admin_member_new', methods: ['GET', 'POST'])]
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

    /**
     * Fiche détaillée d'un membre.
     * Note : aucune vue de l'application ne pointe vers cette route (pas de lien dans
     * index.html.twig ni ailleurs) — page potentiellement orpheline, à confirmer avant suppression.
     */
    #[Route('/admin/member/{id}', name: 'app_admin_member_show', methods: ['GET'])]
    public function show(Member $member): Response
    {
        return $this->render('admin/member/show.html.twig', [
            'member' => $member,
        ]);
    }

    /**
     * Édition d'un membre existant (informations personnelles, structure, rôle...).
     * Sert aussi de page "Mes informations" pour l'utilisateur connecté (lien dans la navbar).
     */
    #[Route('/gestapp/member/{id}/edit', name: 'app_admin_member_edit', methods: ['GET', 'POST'])]
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

            $this->addFlash('success', 'La fiche a bien été mis à jour.');
            return $this->redirectToRoute('app_admin_member_edit', [ 'id' => $member->getId()]);
        }

        return $this->render('admin/member/edit.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    /**
     * Réinitialisation du mot de passe d'un membre, appelée en AJAX depuis la modale de la liste.
     * Réservée au ROLE_SUPER_ADMIN.
     * GET : retourne le formulaire (HTML) encapsulé dans une réponse JSON pour injection dans la modale.
     * POST : valide et hash le nouveau mot de passe ; retourne toujours un code HTTP 200,
     * le succès/échec métier étant porté par le champ `code` du JSON (200 = ok, 400 = formulaire invalide).
     */
    #[Route("/admin/member/renew-password/{id}", name: "app_admin_member_renew_password", methods: ["GET", "POST"])]
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

        if ($form->isSubmitted()) {
            if($form->isValid()){
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
                'code' => 400,
                'message' => 'Une erreur est survenue.',
                'formView' => $view->getContent(),
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

    /**
     * Suppression d'un membre, réservée aux rôles ROLE_ADMIN et ROLE_SUPER_ADMIN
     * (ROLE_ADMIN suffit grâce à la hiérarchie de rôles de security.yaml).
     * Si le membre est un prescripteur, ses bénéficiaires sont détachés (referent = null)
     * avant suppression pour ne pas les laisser orphelins d'une FK invalide.
     * Répond en JSON si l'appel est en AJAX (modale de confirmation côté liste),
     * sinon redirige classiquement avec un flash (formulaire de suppression de la fiche détail).
     */
    #[Route('/admin/member/{id}', name: 'app_admin_member_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Member $member, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $member->getId(), $request->getPayload()->getString('_token'))) {
            if($member->getRoles()[0] === 'ROLE_PRESCRIPTEUR')
            {
                $beneficiaries = $member->getBeneficiaries();
                foreach($beneficiaries as $beneficiary)
                {
                    $beneficiary->setReferent(null);
                    $entityManager->persist($beneficiary);
                }
            }

            $entityManager->remove($member);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'code' => 200,
                    'message' => 'Le membre a bien été supprimé.',
                ]);
            }

            $this->addFlash('danger', 'Le membre a bien été supprimé.');
            return $this->redirectToRoute('app_admin_member_index');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'code' => 400,
                'message' => 'Une erreur est survenue.',
            ]);
        }

        return $this->redirectToRoute('app_admin_member_index');
    }
}
